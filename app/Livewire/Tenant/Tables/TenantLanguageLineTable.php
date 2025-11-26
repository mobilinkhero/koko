<?php

namespace App\Livewire\Tenant\Tables;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Components\SetUp\Exportable;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Traits\WithExport;

final class TenantLanguageLineTable extends PowerGridComponent
{
    use WithExport;

    public string $tableName = 'tenant-language-line-table';

    public bool $deferLoading = true;

    public string $loadingComponent = 'components.custom-loading';

    public string $primaryKey = 'key';

    public string $sortField = 'key';

    public $value;

    public $languageCode;

    public $tenantId;

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

    public function datasource(): \Illuminate\Support\Collection
    {
        $languageData = getLanguageJson($this->languageCode);

        return collect($languageData)->map(function ($value, $key) {
            $item = new \stdClass;
            $item->id = (string) $key;
            $item->key = (string) $key;
            $item->value = $value;
            $item->english = getLangugeValue('en', $key);

            return $item;
        });
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('key', function ($lang) {
                return e($lang->key);
            })
            ->add('english', function ($lang) {
                return e(getLangugeValue('en', $lang->key));
            })
            ->add('value', function ($lang) {
                return e($lang->value);
            });
    }

    public function columns(): array
    {
        return [
            Column::make('English', 'english', 'english')
                ->sortable()
                ->searchable()
                ->bodyAttribute('style', 'width: calc(25 * 3ch); word-wrap: break-word; white-space: normal; line-height: 1.8;'),
            Column::make(is_numeric($name = getLanguage($this->languageCode, ['name'])->name) ? (int) $name : $name, 'value')
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
        $this->tenantId = tenant_id();
        // Retrieve the language data
        $languageData = getLanguageJson($this->languageCode);

        // Check if the key exists in the language data
        $currentValue = getArrayItem($key, $languageData, null);

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

        File::put(resource_path("lang/translations/tenant/{$this->tenantId}/tenant_{$this->languageCode}.json"), json_encode($languageData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $locale = Session::get('locale', config('app.locale'));
        Cache::forget("translations.{$this->tenantId}_tenant_{$locale}");

        $this->notify(['type' => 'success', 'message' => t('translation_updated_successfully')]);
    }
}
