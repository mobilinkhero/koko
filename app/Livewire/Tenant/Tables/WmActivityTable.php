<?php

namespace App\Livewire\Tenant\Tables;

use App\Models\Tenant\WmActivityLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class WmActivityTable extends PowerGridComponent
{
    public string $tableName = 'wm-activity-table';

    public string $sortField = 'wm_activity_logs.created_at';

    public string $sortDirection = 'DESC';

    public bool $deferLoading = true;

    public string $loadingComponent = 'components.custom-loading';

    public function setUp(): array
    {
        $this->showCheckBox();

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

        return WmActivityLog::query()
            ->leftJoin('template_bots', function ($join) {
                $join->on('wm_activity_logs.category_id', '=', 'template_bots.id')
                    ->where('wm_activity_logs.category', '=', 'template_bot');
            })
            ->leftJoin('message_bots', function ($join) {
                $join->on('wm_activity_logs.category_id', '=', 'message_bots.id')
                    ->where('wm_activity_logs.category', '=', 'message_bot');
            })
            ->leftJoin('campaigns', function ($join) {
                $join->on('wm_activity_logs.category_id', '=', 'campaigns.id')
                    ->where('wm_activity_logs.category', '=', 'campaign');
            })
            ->leftJoin('whatsapp_templates', 'template_bots.template_id', '=', 'whatsapp_templates.template_id')
            ->where('wm_activity_logs.tenant_id', tenant_id())
            ->select(
                'wm_activity_logs.*',
                DB::raw('(SELECT COUNT(*) FROM wm_activity_logs i2 WHERE i2.id <= wm_activity_logs.id AND i2.tenant_id = '.$tenantId.') as row_num'),
                DB::raw("COALESCE(template_bots.name, message_bots.name, campaigns.name, '-') as name"),
                DB::raw("
                COALESCE(
                    CASE
                        WHEN wm_activity_logs.category = 'template_bot'
                            AND wm_activity_logs.category_id = template_bots.id
                            THEN (SELECT template_name FROM whatsapp_templates WHERE whatsapp_templates.template_id = template_bots.template_id LIMIT 1)
                        WHEN wm_activity_logs.category = 'campaign'
                            AND wm_activity_logs.category_id = campaigns.id
                            THEN (SELECT template_name FROM whatsapp_templates WHERE whatsapp_templates.template_id = campaigns.template_id LIMIT 1)
                        WHEN wm_activity_logs.category = 'Initiate Chat'
                            THEN (
                            SELECT template_name
                            FROM whatsapp_templates
                             WHERE whatsapp_templates.template_id = JSON_UNQUOTE(JSON_EXTRACT(wm_activity_logs.category_params, '$.templateId'))
                             LIMIT 1)
                        ELSE '-'
                    END, '-') as template_name
                ")
            );
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('category', fn ($model) => t($model->category))
            ->add('template_name', fn ($model) => t($model->template_name))
            ->add(
                'response_code',
                fn ($model) => '<div class="flex justify-center">'.(
                    $model->response_code === '200'
                    ? '<span class="bg-success-100 text-success-800 dark:text-success-400 dark:bg-success-900/20 px-2.5 py-0.5 rounded-full text-xs font-medium">'.$model->response_code.'</span>'
                    : (
                        $model->response_code === '400'
                        ? '<span class="bg-danger-100 text-danger-800 dark:text-danger-400 dark:bg-danger-900/20 px-2.5 py-0.5 rounded-full text-xs font-medium">'.$model->response_code.'</span>'
                        : '<span class="bg-warning-100 text-warning-800 dark:text-warning-400 dark:bg-warning-900/20 px-2.5 py-0.5 rounded-full text-xs font-medium">'.($model->response_code ?? 'N/A').'</span>'
                    )
                ).'</div>'
            )
            ->add('rel_type', function ($model) {
                $class = $model->rel_type == 'lead'
                    ? 'bg-primary-100 text-primary-800 dark:text-primary-400 dark:bg-primary-900/20'
                    : 'bg-success-100 text-success-800 dark:text-success-400 dark:bg-success-900/20';

                return '<div class="flex justify-center"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium '.$class.'">'.ucfirst($model->rel_type).'</span></div>';
            })

            ->add('created_at_formatted', function ($model) {
                return '<div class="relative group">
                        <span class="cursor-default" data-tippy-content="'.format_date_time($model->created_at).'">'
                    .Carbon::parse($model->created_at)->setTimezone(config('app.timezone'))->diffForHumans(['options' => Carbon::JUST_NOW]).'</span>
                    </div>';
            });
    }

    public function columns(): array
    {
        return [
            Column::make(t('SR.NO'), 'row_num')
                ->sortable(),

            Column::make(t('category'), 'category')
                ->sortable()
                ->searchable(),
            Column::make(t('name'), 'name')
                ->sortable(),
            Column::make(t('template_name'), 'template_name', 'whatsapp_templates.template_name')
                ->sortable()
                ->searchable(),
            Column::make(t('response_code'), 'response_code')
                ->sortable()
                ->searchable(),
            Column::make(t('relation_type'), 'rel_type')
                ->sortable()
                ->searchable(),
            Column::make(t('created_at'), 'created_at_formatted', 'created_at')
                ->sortable(),
            Column::action(t('action')),
        ];
    }

    public function actions(WmActivityLog $row): array
    {
        $actions[] = Button::add('View')
            ->slot(t('view'))
            ->id()
            ->class('inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-white bg-primary-600 rounded shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 justify-center')
            ->dispatch('viewLogDetails', ['logId' => $row->id]);

        if (checkPermission('tenant.activity_log.delete')) {
            $actions[] = Button::add('Delete')
                ->slot(t('delete'))
                ->id()
                ->class('inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-white bg-danger-600 rounded shadow-sm hover:bg-danger-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-danger-600 justify-center')
                ->dispatch('confirmDelete', ['logId' => $row->id]);
        }

        return empty($actions) ? ['-'] : $actions;
    }

    public function header(): array
    {
        $buttons = [];

        if (checkPermission('tenant.activity_log.delete')) {
            $buttons[] = Button::add('bulk-delete')
                ->id()
                ->slot(t('bulk_delete').'(<span x-text="window.pgBulkActions.count(\''.$this->tableName.'\')"></span>)')
                ->class('inline-flex items-center justify-center px-3 py-2 text-sm border border-transparent rounded-md font-medium disabled:opacity-50 disabled:pointer-events-none transition bg-danger-600 text-white hover:bg-danger-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-danger-600 absolute md:top-0 top-[116px] left-[100px] lg:left-[120px] sm:left-[136px] sm:top-0 whitespace-nowrap')
                ->dispatch('bulkDelete.'.$this->tableName, []);
        }

        return $buttons;
    }

    #[On('bulkDelete.{tableName}')]
    public function bulkDelete(): void
    {
        $selectedIds = $this->checkboxValues;
        if (! empty($selectedIds) && count($selectedIds) !== 0) {
            $this->dispatch('confirmDelete', $selectedIds);
            $this->checkboxValues = [];
        } else {
            $this->notify(['type' => 'danger', 'message' => t('no_contact_selected')]);
        }
    }
}
