<?php

namespace App\Livewire\Admin\Tables;

use App\Enum\TenantStatus;
use App\Events\Tenant\TenantStatusChanged;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class TenantTable extends PowerGridComponent
{
    public string $tableName = 'tenant-table';

    public string $sortField = 'created_at';

    public string $sortDirection = 'DESC';

    public bool $deferLoading = true;

    public string $loadingComponent = 'components.custom-loading';

    public function setUp(): array
    {
        return [
            PowerGrid::header()
                ->showSearchInput()
                ->showToggleColumns()
                ->withoutLoading(),
            PowerGrid::footer()
                ->showPerPage(perPage: table_pagination_settings()['current'], perPageValues: table_pagination_settings()['options'])
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Tenant::withoutGlobalScopes()
            ->join('users', 'tenants.id', '=', 'users.tenant_id')
            ->join(DB::raw('(
                    SELECT MIN(id) as id
                    FROM users
                    WHERE is_admin = 1
                    GROUP BY tenant_id
                ) as oldest_admins'), 'users.id', '=', 'oldest_admins.id')
            ->select([
                'tenants.id',
                'tenants.company_name',
                'tenants.status',
                'tenants.domain',
                'tenants.subdomain',
                'tenants.created_at',
                'tenants.deleted_date',
                'users.id as user_id',
                'users.firstname',
                'users.lastname',
                'users.email',
                'users.is_admin',
                'users.email_verified_at',
            ]);
    }

    public function rowAttributes(Model $row): array
    {
        if ($row->deleted_date !== null) {
            return [
                'class' => 'bg-red-50 dark:bg-red-900/10',
                'style' => 'background-color: #fef2f2 !important;', // Fallback inline style
            ];
        }

        return [];
    }

    public function fields(): PowerGridFields
    {
        $options = $this->statusSelectOptions();

        return PowerGrid::fields()
            ->add('firstname', function ($user) {
                $loggedInUser = Auth::user();

                // Add deleted indicator styling
                $deletedClass = $user->deleted_date ? 'text-red-600 opacity-75' : 'dark:text-gray-200 text-primary-600 dark:hover:text-primary-400';

                // Start rendering output
                $output = '<div class="group relative inline-block min-h-[40px]">
                <div class="flex items-center gap-3 w-auto min-w-0 max-w-full ">
                    <p class="'.$deletedClass.' text-sm break-words truncate">'.
                    $user->firstname.' '.$user->lastname.
                    '</p>
                </div>

                <!-- Action Links (Hidden by Default, Shown on Hover) -->
                <div class="absolute contact-actions left-[-40px] lg:left-0 top-3 mt-2 space-x-1 text-xs text-gray-600 dark:text-gray-300">';

                $hasPrevious = false;

                if (checkPermission('admin.tenants.view')) {
                    $output .= '<button onclick="Livewire.dispatch(\'viewTenant\', { tenantId: '.$user->id.' })" class="hover:text-info-600">'.t('view').'</button>';
                    $hasPrevious = true;
                }

                if (checkPermission('admin.tenants.edit')) {
                    if ($hasPrevious) {
                        $output .= ' <span class="pt-3 lg:pt-1">|</span>';
                    }
                    $output .= '<button onclick="Livewire.dispatch(\'editTenant\', { tenantId: '.$user->id.' })" class="hover:text-teal-600">'.t('edit').'</button>';
                    $hasPrevious = true;
                }

                if (checkPermission('admin.tenants.delete')) {
                    if ($hasPrevious) {
                        $output .= ' <span class="pt-3 lg:pt-1">|</span>';
                    }

                    if ($user->deleted_date) {
                        // Show restore button for deleted tenants with active subscriptions
                        $output .= '<button onclick="Livewire.dispatch(\'restoreTenant\', { tenantId: '.$user->id.' })" class="hover:text-success-600">'.t('restore').'</button>';
                    } else {
                        // Show delete button with confirmation for active tenants
                        $output .= '<button wire:click="$parent.confirmDelete('.$user->id.')" class="hover:text-danger-600">'.t('delete').'</button>';
                    }
                }
                $output .= '</div></div>';

                return $output;
            })

            ->add('created_at_formatted', function ($user) {
                return '<div class="relative group">
                        <span class="cursor-default" data-tippy-content="'.format_date_time($user->created_at).'">'
                    .Carbon::parse($user->created_at)->diffForHumans(['options' => Carbon::JUST_NOW]).'</span>
                    </div>';
            })
            ->add('status', function ($user) use ($options) {
                if ($user->deleted_date) {
                    // Prepare tooltip content
                    $tooltipContent = t('will_be_deleted');
                    if ($user->deleted_date) {
                        $tooltipContent .= '<br>Deletion date: '.format_date_time($user->deleted_date);
                    }

                    // Show deleted status with red badge and tooltip
                    return '<div class="bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400 px-2.5 py-0.5 rounded-full text-xs font-medium inline-flex items-center cursor-help" data-tippy-content="'.$tooltipContent.'" data-tippy-allowHTML="true">
                        <span class="w-2 h-2 bg-red-500 rounded-full mr-1.5"></span>
                        '.t('will_be_deleted').'
                    </div>';
                }

                return Blade::render(
                    '<x-select-status type="occurrence" :options="$options" :userId="$userId" :selected="$selected"/>',
                    [
                        'options' => $options,
                        'userId' => intval($user->id),
                        'selected' => $user->status,
                    ]
                );
            });
    }

    #[On('statusChanged')]
    public function statusChanged($statusId, $userId)
    {
        if (checkPermission('admin.tenants.edit')) {
            // Find the tenant by ID
            $tenant = Tenant::find($userId);

            if ($tenant) {
                // Store original status for comparison
                $originalStatus = $tenant->status;

                // Update tenant status directly with the string value
                $tenant->status = $statusId;

                // Save the tenant
                $tenant->save();

                Cache::forget("tenant_{$tenant->id}");

                event(new TenantStatusChanged($tenant, $originalStatus, $statusId));

                // Show success notification
                $this->notify([
                    'message' => t('tenant_status_updated', ['status' => ucfirst($statusId)]),
                    'type' => 'success',
                ]);
            } else {
                // Tenant not found - show error
                $this->notify([
                    'message' => t('tenant_not_found'),
                    'type' => 'error',
                ]);
            }
        } else {
            $this->notify(['type' => 'danger', 'message' => t('access_denied_note')], true);

            return redirect(route('admin.tenants.list'));
        }
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')
                ->searchable()
                ->sortable(),

            Column::make('Name', 'firstname', 'users.firstname')
                ->bodyAttribute('class="relative')
                ->searchable()
                ->sortable(),
            Column::make('Email', 'email', 'users.email')
                ->searchable()
                ->sortable(),
            Column::make('Company Name', 'company_name')
                ->searchable()
                ->sortable(),
            Column::make('Status', 'status')
                ->searchable()
                ->sortable(),
            Column::make('Created at', 'created_at_formatted', 'created_at')
                ->searchable(),

            Column::action(t('action'))
                ->hidden(! checkPermission(['admin.tenants.login'])),
        ];
    }

    public function filters(): array
    {
        return [];
    }

    public function statusSelectOptions()
    {
        $labels = TenantStatus::labels();

        if (empty($labels)) {
            // Fallback in case the enum method fails
            return [
                'active' => 'Active',
                'deactive' => 'Deactive',
                'suspended' => 'Suspended',
                'expired' => 'Expired',
            ];
        }

        return $labels;
    }

    public function actions(Tenant $tenant)
    {
        $actions = [];
        if (
            $tenant->is_admin == 1 &&
            empty($tenant->email_verified_at) &&
            get_setting('tenant.isEmailConfirmationEnabled')
        ) {
            $actions[] = Button::add('confirm_registration')
                ->slot('<svg class="w-4 h-4" data-tippy-content="'.t('confirm_registration').'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3" fill="none" stroke="currentColor" stroke-width="1.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>')
                ->id('confirm_registration')
                ->class('inline-flex items-center px-2 py-1 text-xs font-medium text-primary-600 bg-primary-100 rounded hover:bg-primary-200 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:bg-primary-900 dark:text-primary-200 border border-primary-300 dark:hover:border-primary-600')

                ->dispatch('confirmTenantRegistration', ['tenantId' => $tenant->id]);
        }

        if (checkPermission('admin.tenants.login')) {
            $actions[] = Button::add('login_as_tenant')
                ->slot(t('login_as_tenant'))
                ->id()
                ->class('inline-flex items-center justify-center px-3 py-1 text-sm border border-success-300 rounded-md font-medium disabled:opacity-50 disabled:pointer-events-none transition bg-success-100 text-success-700 hover:bg-success-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-success-300 dark:bg-slate-700 dark:border-slate-500 dark:text-success-400 dark:hover:border-success-600 dark:hover:bg-success-600 dark:hover:text-white dark:focus:ring-offset-slate-800')
                ->route('admin.login.as', ['id' => $tenant->user_id]);
        }

        return $actions ?? [];
    }
}
