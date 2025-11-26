<?php

namespace App\Livewire\Tenant\Tables;

use App\Enum\Tenant\WhatsAppTemplateRelationType;
use App\Models\Tenant\MessageBot;
use App\Services\FeatureService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class MessageBotTable extends PowerGridComponent
{
    public string $tableName = 'message-bot-table-hb8oye-table';

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

        return MessageBot::query()
            ->selectRaw('message_bots.*, (SELECT COUNT(*) FROM message_bots i2 WHERE i2.id <= message_bots.id AND i2.tenant_id = ?) as row_num', [$tenantId])
            ->where('tenant_id', $tenantId);
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('row_num')
            ->add('name', function ($messageBot) {

                $output = '<div class="group relative inline-block min-h-[40px]">
                <span>'.e($messageBot->name).'</span>
                <!-- Action Links -->
                <div class="absolute left-[-40px] lg:left-0 top-3 mt-2 pt-1 hidden contact-actions space-x-1 text-xs text-gray-600 dark:text-gray-300">';

                $actions = [];

                if (checkPermission('tenant.message_bot.edit')) {
                    $actions[] = '<a href="'.tenant_route('tenant.messagebot.create', ['messagebotId' => $messageBot->id]).'" class="hover:text-success-600">'.t('edit').'</a>';
                }

                if (checkPermission('tenant.message_bot.delete')) {
                    $actions[] = '<button onclick="Livewire.dispatch(\'confirmDelete\', { messagebotId: '.$messageBot->id.' })" class="hover:text-danger-600">'.t('delete').'</button>';
                }

                if (checkPermission('tenant.message_bot.clone')) {
                    $actions[] = '<button onclick="Livewire.dispatch(\'cloneRecord\', { messagebotId: '.$messageBot->id.' })" class="hover:text-info-600">'.t('clone').'</button>';
                }

                $output .= implode('<span>|</span>', $actions);

                $output .= '</div></div>';

                return $output;
            })
            ->add(
                'rel_type',
                fn ($msgBot) => $msgBot->rel_type === 'lead'
                    ? '<span class="bg-primary-100 text-primary-800 dark:text-primary-400 dark:bg-primary-900/20 px-2.5 py-0.5 rounded-full text-xs font-medium ">'.t($msgBot->rel_type).'</span>'
                    : ($msgBot->rel_type === 'customer'
                        ? '<span class="bg-success-100 text-success-800 dark:text-success-400 dark:bg-success-900/20 px-2.5 py-0.5 rounded-full text-xs font-medium ">'.t($msgBot->rel_type).'</span>'
                        : '<span class="bg-danger-100 ring-1 ring-danger-300 text-danger-800 dark:bg-danger-800 dark:ring-danger-600 dark:text-danger-100 px-3 py-1 rounded-full text-xs font-semibold">'.(t($msgBot->rel_type) ?? 'N/A').'</span>')
            )

            ->add('trigger', function ($model) {
                $replyTextArray = json_decode($model->trigger);

                return is_array($replyTextArray) ? implode(', ', $replyTextArray) : $model->trigger;
            })
            ->add('reply_type_formatted', function ($msgBot) {
                $replyData = WhatsAppTemplateRelationType::getReplyType((int) $msgBot->reply_type);

                return ucfirst($replyData ?? '');
            })

            ->add('created_at_formatted', function ($messageBot) {
                return '<div class="relative group">
                        <span class="cursor-default"  data-tippy-content="'.format_date_time($messageBot->created_at).'">'.\Carbon\Carbon::parse($messageBot->created_at)->diffForHumans(['options' => \Carbon\Carbon::JUST_NOW]).'</span>
                        </div>';
            });
    }

    public function columns(): array
    {
        return [
            Column::make(t('SR.NO'), 'row_num')
                ->sortable(),

            Column::make(t('name'), 'name')
                ->searchable()
                ->sortable(),

            Column::make(t('type'), 'reply_type_formatted', 'reply_type')
                ->searchable()
                ->sortable(),

            Column::make(t('trigger_keyword'), 'trigger')
                ->searchable()
                ->sortable(),

            Column::make(t('relation_type'), 'rel_type')
                ->searchable()
                ->sortable(),

            Column::make(t('active'), 'is_bot_active')
                ->searchable()
                ->sortable()
                ->toggleable(hasPermission: true, trueLabel: '1', falseLabel: '0')
                ->bodyAttribute('flex align-center mt-2'),

            Column::make('Created At', 'created_at_formatted', 'created_at')
                ->sortable(),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::select('rel_type')
                ->dataSource(collect(WhatsAppTemplateRelationType::getRelationtype())
                    ->map(fn ($value, $key) => ['value' => $key, 'label' => ucfirst($value)])
                    ->values()
                    ->toArray())
                ->optionValue('value')
                ->optionLabel('label'),

            // Type filter
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

            Filter::datepicker('created_at'),
        ];
    }

    public function onUpdatedToggleable(string $id, string $field, string $value): void
    {
        if (checkPermission('tenant.message_bot.edit')) {
            $messageBot = MessageBot::find($id);
            if ($messageBot) {
                $messageBot->is_bot_active = ($value === '1') ? 1 : 0;
                $messageBot->save();

                $statusMessage = $messageBot->is_bot_active
                    ? t('message_bot_is_activated')
                    : t('message_bot_is_deactivated');

                $this->notify([
                    'message' => $statusMessage,
                    'type' => 'success',
                ]);
            }
        }
    }

    #[\Livewire\Attributes\On('cloneRecord')]
    public function cloneRecord($messagebotId)
    {
        if (checkPermission('tenant.message_bot.clone')) {
            // Check feature limit before cloning
            if ($this->featureLimitChecker->hasReachedLimit('message_bots', MessageBot::class)) {
                $this->notify([
                    'type' => 'warning',
                    'message' => t('message_bot_limit_reached_upgrade_plan'),
                ]);

                return;
            }

            $existingBot = MessageBot::findOrFail($messagebotId);
            if (! $existingBot) {
                $this->notify(['type' => 'info', 'message' => t('message_bot_not_found')]);

                return false;
            }

            $oldFilePath = $existingBot->filename;
            $newFilePath = null;

            if ($oldFilePath) {
                $folderPath = 'tenant/'.tenant_id().'/message-bot';
                $originalName = pathinfo($oldFilePath, PATHINFO_BASENAME);
                $newFilePath = $originalName;

                if (Storage::disk('public')->exists($oldFilePath)) {
                    Storage::disk('public')->copy($oldFilePath, $newFilePath);
                } else {
                    $newFilePath = null;
                }
            }

            // Clone the bot and update the filename
            $cloneBot = $existingBot->replicate();
            $cloneBot->filename = $newFilePath;
            $this->featureLimitChecker->trackUsage('message_bots');
            $cloneBot->save();

            if ($cloneBot) {
                $this->notify(['type' => 'success', 'message' => t('bot_clone_successfully')], true);

                return redirect()->to(tenant_route('tenant.messagebot.create', ['messagebotId' => $cloneBot->id]));
            }
        }
    }
}
