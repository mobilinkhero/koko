<?php

namespace App\Livewire\Tenant\Tables;

use App\Models\Tenant\WhatsappTemplate;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class WhatsppTemplateTable extends PowerGridComponent
{
    public string $tableName = 'whatspp-template-table-pygsun-table';

    public bool $showFilters = false;

    public bool $deferLoading = true;

    public string $sortDirection = 'desc';

    public string $loadingComponent = 'components.custom-loading';

    public function boot(): void
    {
        config(['livewire-powergrid.filter' => 'outside']);
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

        return WhatsappTemplate::query()
            ->selectRaw('whatsapp_templates.*, (SELECT COUNT(*) FROM whatsapp_templates i2 WHERE i2.id <= whatsapp_templates.id AND i2.tenant_id = ?) as row_num', [$tenantId])
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
            ->add(
                'status',
                fn ($contact) => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium '.
                    match ($contact->status) {
                        'APPROVED' => 'bg-success-100 text-success-800 dark:text-success-400 dark:bg-success-900/20',
                        'REJECTED' => 'bg-danger-100 text-danger-800 dark:text-danger-400 dark:bg-danger-900/20',
                        'PENDING' => 'bg-warning-100 text-warning-800 dark:text-warning-400 dark:bg-warning-900/20',
                        default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                    }.
                    '">'.($contact->status ?? 'N/A').'</span>'
            )
            ->add(
                'header_data_format',
                fn ($contact) => $contact->header_data_format == null ? '-' : $contact->header_data_format
            );
    }

    public function columns(): array
    {
        return [
            Column::make(t('SR.NO'), 'row_num')
                ->sortable(),

            Column::make(t('template_name'), 'template_name')
                ->searchable()
                ->sortable(),

            Column::make(t('languages'), 'language')
                ->searchable()
                ->sortable(),

            Column::make(t('category'), 'category')
                ->searchable()
                ->sortable(),

            Column::make(t('template_type'), 'header_data_format')
                ->searchable()
                ->sortable(),

            Column::make(t('status'), 'status')
                ->searchable()
                ->sortable(),

            Column::make(t('body_data'), 'body_data')
                ->searchable()
                ->sortable()
                ->headerAttribute('text-wrap', 'white-space: normal;')
                ->bodyAttribute('text-wrap', 'white-space: normal; word-wrap: break-word;'),

            Column::action(t('actions'))
                ->hidden(! checkPermission('tenant.template.delete')),
        ];
    }

    public function filters(): array
    {
        return [
            Filter::inputText('template_name')->placeholder('Template Name'),

            $this->createSelectFilter('language'),
            $this->createSelectFilter('category'),
            $this->createFilterForHeaderDataFormat(),
            $this->createSelectFilter('status'),
        ];
    }

    // Regular select filter
    public function createSelectFilter(string $field)
    {
        return Filter::select($field, $field)
            ->dataSource(
                WhatsappTemplate::select($field)
                    ->distinct()
                    ->whereNotNull($field)
                    ->where($field, '!=', '')
                    ->pluck($field)
                    ->map(fn ($value) => ['id' => $value, 'name' => $value])
            )
            ->optionLabel('name')
            ->optionValue('id');
    }

    // Special filter for header_data_format to exclude nulls
    public function createFilterForHeaderDataFormat()
    {
        return Filter::select('header_data_format', 'header_data_format')
            ->dataSource(
                WhatsappTemplate::select('header_data_format')
                    ->distinct()
                    ->whereNotNull('header_data_format')
                    ->where('header_data_format', '!=', '')
                    ->pluck('header_data_format')
                    ->map(fn ($value) => ['id' => $value, 'name' => $value])
            )
            ->optionLabel('name')
            ->optionValue('id');
    }

    /**
     * Handle template deletion
     */
    #[On('deleteTemplate')]
    public function deleteTemplate($templateId, $templateName): void
    {
        if (! checkPermission('tenant.template.delete')) {
            $this->notification([
                'message' => t('access_denied_note'),
                'type' => 'error',
            ]);

            return;
        }

        try {
            $template = WhatsappTemplate::where('id', $templateId)
                ->where('tenant_id', tenant_id())
                ->first();

            if (! $template) {
                $this->notification([
                    'message' => t('template_not_found'),
                    'type' => 'error',
                ]);

                return;
            }

            // Use the WhatsApp trait to delete from Meta and database
            $whatsappTrait = new class
            {
                use \App\Traits\WhatsApp;

                public function getWaTenantId()
                {
                    return tenant_id();
                }
            };

            $result = $whatsappTrait->deleteTemplate($template->template_name, $template->template_id);

            if ($result['status']) {
                $this->notification([
                    'message' => $result['message'],
                    'type' => 'success',
                ]);

                // Refresh the table
                $this->dispatch('pg:eventRefresh-whatspp-template-table-pygsun-table');
            } else {
                $this->notification([
                    'message' => $result['message'],
                    'type' => 'error',
                ]);
            }
        } catch (\Exception $e) {
            whatsapp_log('Template deletion error in table', 'error', [
                'template_id' => $templateId,
                'template_name' => $templateName,
                'error' => $e->getMessage(),
                'tenant_id' => tenant_id(),
            ], $e);

            $this->notification([
                'message' => t('template_deletion_failed').': '.$e->getMessage(),
                'type' => 'error',
            ]);
        }
    }

    /**
     * Actions for each template row
     */
    public function actions(WhatsappTemplate $row): array
    {
        $actions = [];
        if (checkPermission('tenant.template.edit') && ($row->status == 'APPROVED' || $row->status == 'PENDING')) {
            $actions[] = Button::add('edit')
                ->slot('<svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
                ')
                ->id()
                ->class('inline-flex items-center gap-1 px-2 py-1 text-sm font-medium text-info-800 bg-info-100 rounded shadow-sm hover:bg-info-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-info-600 justify-center')
                ->dispatch('showEditPage', [
                    'templateId' => $row->id,

                ]);
        }
        if (checkPermission('tenant.template.delete')) {
            $actions[] = Button::add('delete')
                ->slot('<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>')
                ->id()
                ->class('inline-flex items-center gap-1 px-2 py-1 text-sm font-medium text-danger-500 bg-danger-100 rounded shadow-sm hover:bg-danger-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-danger-600 justify-center')
                ->dispatch('showDeleteConfirmation', [
                    'templateId' => $row->id,
                    'templateName' => $row->template_name,
                    'templateMetaId' => $row->template_id,
                ]);
        }

        return $actions;
    }
}
