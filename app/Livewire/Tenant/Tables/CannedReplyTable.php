<?php

namespace App\Livewire\Tenant\Tables;

use App\Models\Tenant\CannedReply;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\Facades\Rule;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class CannedReplyTable extends PowerGridComponent
{
    public string $tableName = 'canned-reply-table-ysrvwi-table';

    public bool $deferLoading = false;

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public string $loadingComponent = 'components.custom-loading';

    public function setUp(): array
    {
        return [
            PowerGrid::header()
                ->showSearchInput()
                ->withoutLoading(),
            PowerGrid::footer()
                ->showPerPage(perPage: table_pagination_settings()['current'], perPageValues: table_pagination_settings()['options'])
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        $tenantId = tenant_id();
        $query = CannedReply::query()
            ->selectRaw('*, (SELECT COUNT(*) FROM canned_replies i2 WHERE i2.id <= canned_replies.id AND i2.tenant_id = ?) as row_num', [$tenantId])
            ->where('tenant_id', tenant_id());

        // Additional filtering for non-admin users
        if (! auth()->user()->is_admin) {
            $query->where(function ($q) {
                $q->where('is_public', 1)
                    ->orWhere('added_from', auth()->user()->id);
            });
        }

        return $query;
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
            ->add('created_at');
    }

    public function columns(): array
    {
        return [
            Column::make(t('SR.NO'), 'row_num')
                ->sortable(),

            Column::make(t('title'), 'title')
                ->sortable()
                ->searchable(),

            Column::make(t('description'), 'description')
                ->sortable()
                ->searchable(),

            Column::make(t('public'), 'is_public')
                ->sortable()
                ->toggleable(hasPermission: true, trueLabel: t('public'))
                ->searchable()
                ->bodyAttribute('flex align-center mt-2'),

            Column::action(t('action'))
                ->bodyAttribute('wire:key', 'action_{{ $id }}')
                ->hidden(! checkPermission(['tenant.canned_reply.edit', 'tenant.canned_reply.delete'])),

        ];
    }

    public function filters(): array
    {
        return [];
    }

    public function actions(CannedReply $canned)
    {
        $actions = [];
        if (checkPermission('tenant.canned_reply.edit')) {
            $actions[] = Button::add('edit')
                ->slot(t('edit'))
                ->id()
                ->class('inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-white bg-primary-600 rounded shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 justify-center')
                ->dispatch('editCannedPage', ['cannedId' => $canned->id]);
        }

        if (checkPermission('tenant.canned_reply.delete')) {
            $actions[] = Button::add('delete')
                ->slot(t('delete'))
                ->id()
                ->class('inline-flex items-center gap-2 px-3 py-1 text-sm font-medium text-white bg-danger-600 rounded shadow-sm hover:bg-danger-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-danger-600 justify-center')
                ->dispatch('confirmDelete', ['cannedId' => $canned->id]);
        }

        return $actions;
    }

    public function actionRules($row): array
    {
        $user = auth()->user();

        return [
            Rule::checkbox()
                ->when(
                    fn ($canned) => $canned->added_from !== $user->id && ! $user->is_admin == true
                )
                ->hide(),

            Rule::rows()
                ->when(fn ($canned) => $canned->added_from !== $user->id && ! $user->is_admin == true)
                ->hideToggleable(),
        ];
    }

    public function onUpdatedToggleable(string $id, string $field, string $value): void
    {
        if (checkPermission('tenant.canned_reply.edit')) {
            $cannedReply = CannedReply::find($id);
            if ($cannedReply) {
                $cannedReply->is_public = ($value === '1') ? 1 : 0;
                $cannedReply->save();

                $statusMessage = $cannedReply->is_public
                    ? t('canned_reply_activate')
                    : t('canned_reply_deactivate');

                $this->notify([
                    'message' => $statusMessage,
                    'type' => 'success',
                ]);
            }
        }
    }
}
