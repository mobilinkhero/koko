<?php

namespace App\Livewire\Admin\Tables;

use App\Models\TenantLanguage;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class TenantLanguageTable extends PowerGridComponent
{
    public string $tableName = 'tenant-language-table-table';

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
        return TenantLanguage::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('code')
            ->add('created_at');
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('Name', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Code', 'code')
                ->sortable()
                ->searchable(),

            Column::action('Action'),
        ];
    }

    public function filters(): array
    {
        return [];
    }

    public function actions(TenantLanguage $row): array
    {
        if ($row->code === 'en' || strcasecmp($row->name, 'English') === 0) {
            return [];
        }

        $buttons = [];

        // Add translate button as a standard action
        $buttons[] = Button::add('translate')
            ->slot(t('translate'))
            ->class('inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-white bg-success-600 rounded shadow-sm hover:bg-success-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-success-600')
            ->dispatch('translateLanguage', ['code' => $row->code]);

        // Download button
        $buttons[] = Button::add('download')
            ->slot(t('download'))
            ->class('inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-white bg-info-600 rounded shadow-sm hover:bg-info-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-info-600')
            ->dispatch('downloadLanguage', ['languageId' => $row->id]);

        // Edit button
        $buttons[] = Button::add('edit')
            ->slot(t('edit'))
            ->class('inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-white bg-primary-600 rounded shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 justify-center')
            ->dispatch('editLanguage', ['languageCode' => $row->code]);

        // Delete button
        $buttons[] = Button::add('delete')
            ->slot(t('delete'))
            ->class('inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-white bg-danger-600 rounded shadow-sm hover:bg-danger-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-danger-600')
            ->dispatch('confirmDelete', ['languageId' => $row->id]);

        return $buttons;
    }
}
