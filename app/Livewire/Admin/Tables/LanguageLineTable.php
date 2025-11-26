<?php

namespace App\Livewire\Admin\Tables;

use App\Models\Language;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

final class LanguageLineTable extends PowerGridComponent
{
    use WithExport;

    public bool $isTenantMode = false;

    public string $tableName = 'language-line-table';

    public bool $deferLoading = true;

    public string $loadingComponent = 'components.custom-loading';

    public string $primaryKey = 'key';

    public string $sortField = 'key';

    public $value;

    public $languageCode;

    public function boot()
    {
        if (str_contains(request()->url(), '/tenant-languages/')) {
            $this->isTenantMode = true;
        }
    }

    public function setUp(): array
    {
        return [
            PowerGrid::header()
                ->withoutLoading()
                ->showToggleColumns()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage(perPage: table_pagination_settings()['current'], perPageValues: table_pagination_settings()['options'])
                ->showRecordCount(),
            PowerGrid::exportable('export-language')
                ->striped()
                ->type(Exportable::TYPE_CSV),
        ];
    }

    private $englishData = null;

    private function getEnglishData(): array
    {
        if ($this->englishData === null) {
            // For tenant mode, get English data from resources/lang/tenant_en.json
            // For admin mode, get from resources/lang/en.json
            $englishFilePath = $this->isTenantMode
                ? resource_path('lang/tenant_en.json')
                : resource_path('lang/en.json');

            $this->englishData = [];
            if (file_exists($englishFilePath)) {
                $this->englishData = json_decode(file_get_contents($englishFilePath), true) ?? [];
            }
        }

        return $this->englishData;
    }

    public function datasource(): \Illuminate\Support\Collection
    {
        // Load English data first
        $englishData = $this->getEnglishData();

        // Get current language data
        $filePath = $this->getLanguageFilePath($this->languageCode);
        $languageData = [];
        if (file_exists($filePath)) {
            $languageData = json_decode(file_get_contents($filePath), true) ?? [];
        }

        // Convert English data to collection with all available keys
        return collect($englishData)->map(function ($englishValue, $key) use ($languageData) {
            $item = new \stdClass;
            $item->id = (string) $key;
            $item->key = (string) $key;
            $item->value = $languageData[$key] ?? '';  // Empty string for untranslated lines
            $item->english = $englishValue;

            return $item;
        });
    }

    public function fields(): PowerGridFields
    {
        $englishData = $this->getEnglishData();

        return PowerGrid::fields()
            ->add('id')
            ->add('key', function ($lang) {
                return e($lang->key);
            })
            ->add('english', function ($lang) {
                return e($lang->english);
            })
            ->add('value', function ($lang) {
                return e($lang->value ?: '');  // Show empty string for untranslated lines
            });
    }

    public function columns(): array
    {
        return [
            Column::make('English', 'english', 'english')
                ->sortable()
                ->searchable()
                ->bodyAttribute('style', 'width: calc(25 * 3ch); word-wrap: break-word; white-space: normal; line-height: 1.8;'),
            Column::make($this->getLanguageColumnName(), 'value')
                ->sortable()
                ->searchable()
                ->editOnClick(hasPermission: true, saveOnMouseOut: true)
                ->headerAttribute('text-wrap', 'white-space: normal;')
                ->bodyAttribute('class', 'w-40')
                ->bodyAttribute('style', 'max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;'),
        ];
    }

    public function onUpdatedEditable(string|int $key, string $field, string $value): void
    {
        // Get the correct file path
        $filePath = $this->getLanguageFilePath($this->languageCode);

        // Retrieve the language data
        $languageData = [];
        if (file_exists($filePath)) {
            $languageData = json_decode(file_get_contents($filePath), true) ?? [];
        }

        // Get the current value or empty string for new translations
        $currentValue = $languageData[$key] ?? '';

        // Normalize spaces (replace non-breaking spaces and trim)
        $normalize = fn ($val) => trim(str_replace("\u{A0}", ' ', $val ?? ''));

        $normalizedCurrent = $normalize($currentValue);
        $value = $normalize($value);

        // If the normalized value hasn't changed, return early (no update needed)
        if ($normalizedCurrent === $value) {
            return;
        }

        // Validate and sanitize the input value
        $value = strip_tags($value);
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

        // Prevent JSON injection (optional)
        if (preg_match('/^[\[\{].*[\]\}]$/', trim($value))) {
            $this->notify(['type' => 'danger', 'message' => t('the_translation_cannot_be_a_JSON_object_or_array')]);

            return;
        }

        // Update only if value has changed
        $languageData[$key] = e($value);

        $filePath = $this->getLanguageFilePath($this->languageCode);

        File::put($filePath, json_encode($languageData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $locale = Session::get('locale', config('app.locale'));
        Cache::forget("translations.{$locale}");

        $this->notify(['type' => 'success', 'message' => t('translation_updated_successfully')]);
    }

    private function getLanguageColumnName(): string
    {
        // Query the appropriate model based on tenant mode
        $language = $this->isTenantMode
            ? \App\Models\TenantLanguage::where('code', $this->languageCode)->first()
            : \App\Models\Language::where('code', $this->languageCode)->first();

        if (! $language) {
            return 'Translation';
        }

        $name = $language->name;

        return is_numeric($name) ? (int) $name : $name;
    }

    private function getLanguageFilePath(string $code): string
    {
        if ($code === 'en') {
            return $this->isTenantMode
                ? resource_path('lang/tenant_en.json')
                : resource_path('lang/en.json');
        }

        return $this->isTenantMode
            ? public_path("lang/tenant_{$code}.json")
            : resource_path("lang/translations/{$code}.json");
    }
}
