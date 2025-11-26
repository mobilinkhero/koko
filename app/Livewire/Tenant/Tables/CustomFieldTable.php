<?php

namespace App\Livewire\Tenant\Tables;

use App\Models\Tenant\CustomField;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class CustomFieldTable extends PowerGridComponent
{
    public string $tableName = 'custom-fields-table';

    public bool $deferLoading = true;

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public function setUp(): array
    {
        return [
            PowerGrid::header()
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

        return CustomField::query()
            ->selectRaw('*, (SELECT COUNT(*) FROM `custom_fields` i2 WHERE i2.id <= custom_fields.id AND i2.tenant_id = ?) as row_num', [$tenantId])
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
            ->add('field_label', function ($field) {
                $output = '<div class="group relative inline-block min-h-[40px]">
                <span>'.e($field->field_label).'</span>
                <div class="absolute left-[-40px] lg:left-0 top-3 mt-2 pt-1 hidden contact-actions space-x-1 text-xs text-gray-600 dark:text-gray-300">';

                $actions = [];

                if (checkPermission('tenant.custom_fields.edit')) {
                    $actions[] = '<a href="'.route('tenant.custom-fields.edit', ['subdomain' => tenant_subdomain(), 'customFieldId' => $field->id]).'" class="hover:text-success-600">'.t('edit').'</a>';
                }

                if (checkPermission('tenant.custom_fields.delete')) {
                    $actions[] = '<button onclick="Livewire.dispatch(\'confirmDelete\', { fieldId: '.$field->id.' })" class="hover:text-danger-600">'.t('delete').'</button>';
                }

                $output .= implode('<span>|</span>', $actions);

                $output .= '</div></div>';

                return $output;
            })
            ->add('field_name')
            ->add('field_type')
            ->add('field_type_label', fn ($item) => $this->getFieldTypeLabel($item->field_type))
            ->add('is_required')
            ->add('is_active')
            ->add('show_on_table')
            ->add('created_at_formatted', function ($contact) {
                return '<div class="relative group">
                        <span class="cursor-default" data-tippy-content="'.format_date_time($contact->created_at).'">'
                    .Carbon::parse($contact->created_at)->diffForHumans(['options' => Carbon::JUST_NOW]).'</span>
                    </div>';
            });
    }

    public function columns(): array
    {
        return [
            Column::make(t('SR.NO'), 'row_num')
                ->sortable(),

            Column::make(t('custom_field_name'), 'field_label')
                ->sortable()
                ->searchable(),

            Column::make('Field Name', 'field_name')
                ->sortable()
                ->searchable(),

            Column::make(t('active'), 'is_active')
                ->toggleable(
                    hasPermission: checkPermission('tenant.custom_fields.edit'),
                    trueLabel: t('active'),
                    falseLabel: 'Inactive'
                )->bodyAttribute('flex mt-2 mx-3'),

            Column::make(t('custom_field_type'), 'field_type_label')
                ->sortable(),

            Column::make(t('custom_field_required'), 'is_required')
                ->toggleable(
                    hasPermission: checkPermission('tenant.custom_fields.edit'),
                    trueLabel: t('required'),
                    falseLabel: 'Optional'
                )
                ->bodyAttribute('flex mt-2 mx-3'),

            Column::make(t('custom_field_show_on_table'), 'show_on_table')
                ->toggleable(
                    hasPermission: checkPermission('tenant.custom_fields.edit'),
                    trueLabel: t('show'),
                    falseLabel: 'Optional'
                ),

            Column::make(t('created_at'), 'created_at_formatted', 'created_at')
                ->sortable(),
        ];
    }

    public function filters(): array
    {
        return [];
    }

    private function getFieldTypeLabel($type): string
    {
        $types = [
            'text' => t('text_field'),
            'textarea' => t('textarea_field'),
            'number' => t('number_field'),
            'date' => t('date_field'),
            'dropdown' => t('dropdown_field'),
        ];

        return $types[$type] ?? $type;
    }

    public function onUpdatedToggleable(string $id, string $field, string $value): void
    {
        if (! checkPermission('tenant.custom_fields.edit')) {
            $this->notify([
                'message' => t('access_denied_note'),
                'type' => 'warning',
            ]);

            return;
        }

        $customField = CustomField::where('tenant_id', tenant_id())->find($id);

        if (in_array($field, ['is_required', 'is_active', 'show_on_table'])) {
            $customField->{$field} = $value === '1' ? 1 : 0;
            $customField->save();

            $this->dispatch('refreshComponent');

            $message = t('custom_field_updated_successfully');

            $this->notify([
                'message' => $message,
                'type' => 'success',
            ]);
        }

        Cache::forget('contacts_table_custom_fields'.tenant_id());
    }
}
