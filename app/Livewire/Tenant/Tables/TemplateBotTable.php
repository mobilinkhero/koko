<?php

namespace App\Livewire\Tenant\Tables;

use App\Enum\Tenant\WhatsAppTemplateRelationType;
use App\Models\Tenant\TemplateBot;
use App\Services\FeatureService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class TemplateBotTable extends PowerGridComponent
{
    public string $tableName = 'template-bot-table';

    public string $sortField = 'created_at';

    public string $sortDirection = 'DESC';

    public bool $showFilters = false;

    public bool $deferLoading = true;

    public string $loadingComponent = 'components.custom-loading';

    protected FeatureService $featureLimitChecker;

    public function boot(FeatureService $featureLimitChecker): void
    {
        config(['livewire-powergrid.filter' => 'outside']);
        $this->featureLimitChecker = $featureLimitChecker;
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
        ];
    }

    public function datasource(): Builder
    {
        $tenantId = tenant_id();

        return TemplateBot::query()
            ->selectRaw('template_bots.*, (SELECT COUNT(*) FROM template_bots i2 WHERE i2.id <= template_bots.id AND i2.tenant_id = ?) as row_num', [$tenantId])
            ->where('tenant_id', $tenantId);
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('row_num')
            ->add('id')
            ->add('name', function ($tempBot) {
                $output = '<div class="group relative inline-block min-h-[40px]">
                    <span>'.e($tempBot->name).'</span>
                    <!-- Action Links -->
                    <div class="absolute left-[-40px] lg:left-0 top-3 mt-2 pt-1 hidden contact-actions space-x-1 text-xs text-gray-600 dark:text-gray-300">';

                $actions = [];

                if (checkPermission('tenant.template_bot.edit')) {
                    $actions[] = '<a href="'.tenant_route('tenant.templatebot.create', ['templatebotId' => $tempBot->id]).'" class="hover:text-success-600">'.t('edit').'</a>';
                }

                if (checkPermission('tenant.template_bot.delete')) {
                    $actions[] = '<button onclick="Livewire.dispatch(\'confirmDelete\', { templatebotId: '.$tempBot->id.' })" class="hover:text-danger-600">'.t('delete').'</button>';
                }

                if (checkPermission('tenant.template_bot.clone')) {
                    $actions[] = '<button onclick="Livewire.dispatch(\'cloneRecord\', { templatebotId: '.$tempBot->id.' })" class="hover:text-info-600">'.t('clone').'</button>';
                }

                $output .= implode('<span>|</span>', $actions);
                $output .= '</div></div>';

                return $output;
            })
            ->add('reply_type_formatted', function ($templateBot) {
                return ucfirst(WhatsAppTemplateRelationType::getReplyType($templateBot->reply_type) ?? '');
            })

            ->add('rel_type', function ($templateBot) {
                $class = $templateBot->rel_type == 'lead'
                    ? 'bg-primary-100 text-primary-800 dark:text-primary-400 dark:bg-primary-900/20'
                    : 'bg-success-100 text-success-800 dark:text-success-400 dark:bg-success-900/20';

                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium '.$class.'">'.t($templateBot->rel_type).'</span>';
            })
            ->add('created_at_formatted', function ($templateBot) {
                return '<div class="relative group">
                        <span class="cursor-default"  data-tippy-content="'.format_date_time($templateBot->created_at).'">'.\Carbon\Carbon::parse($templateBot->created_at)->diffForHumans(['options' => \Carbon\Carbon::JUST_NOW]).'</span>
                        </div>';
            });
    }

    public function columns(): array
    {
        return [
            Column::make(t('SR.NO'), 'row_num')
                ->sortable(),

            Column::make(t('name'), 'name')
                ->sortable()
                ->searchable(),

            Column::make(t('reply_type'), 'reply_type_formatted', 'reply_type')
                ->sortable()
                ->searchable(),

            Column::make(t('trigger_keyword'), 'trigger')
                ->sortable()
                ->searchable(),

            Column::make(t('relation_type'), 'rel_type')
                ->sortable()
                ->searchable(),

            Column::make(t('active'), 'is_bot_active')
                ->searchable()
                ->sortable()
                ->toggleable(hasPermission: true, trueLabel: '1', falseLabel: '0')
                ->bodyAttribute('flex align-center mt-2'),

            Column::make(t('created_at'), 'created_at_formatted', 'created_at')
                ->sortable(),
        ];
    }

    public function filters(): array
    {
        return [
            // Reply Type Filter
            Filter::select('reply_type')
                ->dataSource(collect(WhatsAppTemplateRelationType::getReplyType())
                    ->map(fn ($value, $key) => [
                        'value' => $key,
                        'label' => ucfirst($value['label'] ?? ''),
                    ])
                    ->values()
                    ->toArray())
                ->optionValue('value')
                ->optionLabel('label'),

            // RelationType Filter
            Filter::select('rel_type')
                ->dataSource(collect(WhatsAppTemplateRelationType::getRelationtype())
                    ->map(fn ($value, $key) => ['value' => $key, 'label' => ucfirst($value)])
                    ->values()
                    ->toArray())
                ->optionValue('value')
                ->optionLabel('label'),

            Filter::datepicker('created_at'),
        ];
    }

    #[\Livewire\Attributes\On('edit')]
    public function edit($templatebotId)
    {
        if (checkPermission('tenant.template_bot.edit')) {
            return redirect(tenant_route('tenant.templatebot.create', ['templatebotId' => $templatebotId]));
        }
    }

    public function onUpdatedToggleable(string $id, string $field, string $value): void
    {
        if (checkPermission('tenant.template_bot.edit')) {
            $templateBot = TemplateBot::find($id);
            if ($templateBot) {
                $templateBot->is_bot_active = ($value === '1') ? 1 : 0;
                $templateBot->save();

                $statusMessage = $templateBot->is_bot_active
                    ? t('template_bot_activate')
                    : t('template_bot_deactivate');

                $this->notify([
                    'message' => $statusMessage,
                    'type' => 'success',
                ]);
            }
        }
    }

    #[\Livewire\Attributes\On('cloneRecord')]
    public function cloneRecord($templatebotId)
    {
        if (checkPermission('tenant.template_bot.clone')) {
            // Check feature limit before cloning
            if ($this->featureLimitChecker->hasReachedLimit('template_bots', TemplateBot::class)) {
                $this->notify([
                    'type' => 'warning',
                    'message' => t('template_bot_limit_reached_upgrade_plan'),
                ]);

                return;
            }

            $existingBot = TemplateBot::findOrFail($templatebotId);
            if (! $existingBot) {
                $this->notify(['type' => 'info', 'message' => t('template_bot_not_found')]);

                return false;
            }

            $oldFilePath = $existingBot->filename;
            $newFilePath = null;

            if ($oldFilePath) {
                $folderPath = 'tenant/'.tenant_id().'/template-bot';
                $fileName = pathinfo($oldFilePath, PATHINFO_BASENAME);

                $fileParts = explode('_', $fileName);
                $originalName = isset($fileParts[2]) ? $fileParts[2] : $fileName;
                $newFileName = time().'_'.$originalName;
                $newFilePath = $folderPath.'/'.$newFileName;

                if (Storage::disk('public')->exists($oldFilePath)) {
                    Storage::disk('public')->copy($oldFilePath, $newFilePath);
                } else {
                    $newFilePath = null;
                }
            }

            // Clone the bot and update the filename
            $cloneBot = $existingBot->replicate();
            $cloneBot->filename = $newFilePath;
            $this->featureLimitChecker->trackUsage('template_bots');
            $cloneBot->save();

            if ($cloneBot) {
                $this->notify(['type' => 'success', 'message' => t('bot_clone_successfully')], true);

                return redirect()->to(tenant_route('tenant.templatebot.create', ['templatebotId' => $cloneBot->id]));
            }
        }
    }
}
