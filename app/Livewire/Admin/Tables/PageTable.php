<?php

namespace App\Livewire\Admin\Tables;

use App\Models\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class PageTable extends PowerGridComponent
{
    public string $tableName = 'page-table';

    public string $sortField = 'created_at';

    public string $sortDirection = 'DESC';

    public bool $deferLoading = true;

    public string $loadingComponent = 'components.custom-loading';

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
        return Page::query();
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('title', function ($row) {
                return e(truncate_text($row->title, 50));
            })
            ->add('slug', function ($row) {
                return e(truncate_text($row->slug, 50));
            })
            ->add('status')
            ->add('order');
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('Title', 'title')
                ->sortable()
                ->searchable(),

            Column::make('Slug', 'slug')
                ->sortable()
                ->searchable(),

            Column::make('Active', 'status')
                ->sortable()
                ->searchable()
                ->toggleable(hasPermission: true, trueLabel: '1', falseLabel: '0')
                ->bodyAttribute('flex align-center mt-2'),

            Column::make('Order', 'order')
                ->sortable()
                ->searchable(),

            Column::action(t('action'))
                ->hidden(! checkPermission(['admin.pages.edit', 'admin.pages.delete'])),
        ];
    }

    public function filters(): array
    {
        return [];
    }

    public function actions(Page $page): array
    {
        $actions = [];

        if (checkPermission('admin.pages.edit')) {
            $actions[] = Button::add('edit')
                ->slot(t('edit'))
                ->id()
                ->class('inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-white bg-primary-600 rounded shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 justify-center')
                ->dispatch('editPage', ['pageId' => $page->id]);
        }

        if (checkPermission('admin.pages.delete')) {
            $actions[] = Button::add('delete')
                ->slot(t('delete'))
                ->id()
                ->class('inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-white bg-danger-600 rounded shadow-sm hover:bg-danger-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-danger-600 justify-center')
                ->dispatch('confirmDelete', ['pageId' => $page->id]);
        }

        return $actions ?? [];
    }

    public function onUpdatedToggleable(string $id, string $field, string $value): void
    {
        if (checkPermission('admin.pages.edit')) {
            $page = Page::find($id);
            if ($page) {
                $page->status = ($value === '1') ? 1 : 0;
                $page->save();

                $statusMessage = $page->status
                    ? t('page_active')
                    : t('page_deactive');

                $this->notify([
                    'message' => $statusMessage,
                    'type' => 'success',
                ]);
                Cache::forget('menu_items');
            }
        } else {
            $this->notify([
                'message' => t('no_permission_to_perform_action'),
                'type' => 'warning',
            ]);
        }
    }
}
