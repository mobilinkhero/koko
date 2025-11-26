<?php

namespace App\Livewire\Admin\Tables;

use App\Models\Subscription;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class SubscriptionTable extends PowerGridComponent
{
    public string $tableName = 'subscription-table-t5n8qk-table';

    public bool $deferLoading = true;

    public bool $showFilters = false;

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public string $loadingComponent = 'components.custom-loading';

    public array $dateRange = [];

    public function boot()
    {
        config(['livewire-powergrid.filter' => 'outside']);

        if (request()->routeIs('admin.subscriptions.list')) {
            Session::put('last_viewed_subscriptions', now()->toDateTimeString());
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
        ];
    }

    public function datasource(): Builder
    {
        return Subscription::query()->with(['plan', 'tenant']);
    }

    public function relationSearch(): array
    {
        return [
            'plan' => [
                'name' => 'name',
            ],
            'tenant' => [
                'company_name' => 'company_name',
            ],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('tenant', function ($subscription) {
                return $subscription->tenant->company_name;
            })
            ->add('plan', function ($subscription) {
                return $subscription->plan->name;
            })

            ->add('status', function ($subscription) {
                if ($subscription->isActive()) {
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 dark:bg-success-900/50 text-success-800 dark:text-success-400 mr-2">'.t('active').'</span>';
                } elseif ($subscription->isTrial()) {
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900/50 text-purple-800 dark:text-purple-400 mr-2">'.t('trial').'</span>';
                } elseif ($subscription->isCancelled()) {
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-warning-100 dark:bg-warning-900/50 text-warning-800 dark:text-warning-400 mr-2">'.t('cancelled').'</span>';
                } elseif ($subscription->isEnded()) {
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-900/50 text-gray-800 dark:text-gray-400 mr-2">'.t('ended').'</span>';
                } elseif ($subscription->isPause()) {
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-warning-100 dark:bg-warning-900/50 text-warning-800 dark:text-warning-400 mr-2">'.t('paused').'</span>';
                } elseif ($subscription->isNew()) {
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-info-100 dark:bg-info-900/50 text-info-800 dark:text-info-400 mr-2">'.t('new').'</span>';
                } elseif ($subscription->isTerminated()) {
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-danger-100 dark:bg-danger-900/50 text-danger-800 dark:text-danger-400 mr-2">'.t('terminated').'</span>';
                }
            })
            ->add('current_plan_ends_at', function ($subscription) {
                if ($subscription->isTrial()) {
                    $date = $subscription->trial_ends_at;
                } else {
                    $date = $subscription->current_period_ends_at;
                }

                return '<div class="relative group">
                <span class="cursor-default" data-tippy-content="'.format_date_time($date).'">'
                    .Carbon::parse($date)->diffForHumans(['options' => Carbon::JUST_NOW]).'</span>
            </div>';
            });
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')
                ->searchable()
                ->sortable(),

            Column::make('Tenant', 'tenant', 'tenant_id')
                ->sortable()
                ->searchable(),

            Column::make('Plan', 'plan', 'plan_id')
                ->sortable()
                ->searchable(),

            Column::make('Status', 'status')
                ->searchable()
                ->sortable(),

            Column::make('Period Ends At', 'current_plan_ends_at'),

            Column::action('Action')
                ->hidden(! checkPermission('admin.subscription.view')),
        ];
    }

    public function filters(): array
    {
        return [];
    }

    public function actions(Subscription $row): array
    {
        $actions = [];

        if (checkPermission('admin.subscription.view')) {
            $actions[] = Button::add('edit')
                ->slot('View Details')
                ->class('inline-flex items-center justify-center px-3 py-1 text-sm border border-info-300 rounded-md font-medium disabled:opacity-50 disabled:pointer-events-none transition bg-info-100 text-info-700 hover:bg-info-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-info-300 dark:bg-slate-700 dark:border-slate-500 dark:text-info-400 dark:hover:border-info-600 dark:hover:bg-info-600 dark:hover:text-white dark:focus:ring-offset-slate-800')
                ->route('admin.subscriptions.show', [$row->id]);
        }

        return $actions ?? [];
    }
}
