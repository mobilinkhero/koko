<?php

namespace App\Livewire\Admin\Tables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class UserTable extends PowerGridComponent
{
    public string $tableName = 'user-table';

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

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
        return User::query()
            ->where('id', '!=', auth()->id())
            ->where('user_type', '=', 'admin');
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()

            ->add('firstname', function ($user) {

                $deleteAction = "Livewire.dispatch('confirmDelete', { userId: {$user->id} })";

                // Determine profile image
                $profile_img = $user->avatar && Storage::disk('public')->exists($user->avatar)
                    ? asset('storage/'.$user->avatar)
                    : asset('img/user-placeholder.jpg');

                $loggedInUser = Auth::user();

                $fullname = $user->firstname.' '.$user->lastname;

                // Start rendering output
                $output = '<div class="group relative inline-block min-h-[40px]">
    <div class="flex items-center gap-3 w-auto min-w-0 max-w-full ">
        <img src="'.$profile_img.'" class="inline-block object-cover h-8 w-8 rounded-full">
        <p class="dark:text-gray-200 text-primary-600 dark:hover:text-primary-400 text-sm break-words truncate">'.truncate_text($fullname, 50).'</p>
    </div>

    <!-- Action Links (Hidden by Default, Shown on Hover) -->
    <div class="absolute contact-actions dark:text-gray-300 group-hover:flex hidden left-0 mt-5 space-x-1 text-gray-600 text-xs top-3">';

                if (checkPermission('admin.users.view')) {
                    $output .= ' <button onclick="Livewire.dispatch(\'viewUser\', { userId: '.$user->id.' })" class="hover:text-info-600">'.t('view').'</button>';
                }

                if (checkPermission('admin.users.edit')) {
                    $output .= ' <span>|</span><button onclick="Livewire.dispatch(\'editUser\', { userId: '.$user->id.' })" class="hover:text-success-600">'.t('edit').'</button>';
                }

                if (checkPermission('admin.users.delete')) {
                    // NEW LOGIC: Can delete only if target user is NOT admin AND is NOT current user
                    if ($user->is_admin !== true && $loggedInUser->id !== $user->id) {
                        $output .= '<span>|</span>
                <button onclick="'.$deleteAction.'" class="hover:text-danger-600">'.t('delete').'</button>';
                    }
                }

                $output .= '</div>
    </div>
</div>';

                return $output;
            })
            ->add('created_at_formatted', function ($user) {
                return '<div class="relative group">
                        <span class="cursor-default" data-tippy-content="'.format_date_time($user->created_at).'">'
                    .Carbon::parse($user->created_at)->diffForHumans(['options' => Carbon::JUST_NOW]).'</span>
                    </div>';
            });
    }

    public function columns(): array
    {
        return [
            Column::make(t('id'), 'id'),
            Column::make(t('name'), 'firstname')
                ->bodyAttribute('relative mb-2')
                ->sortable()
                ->searchable(),

            Column::make(t('phone'), 'phone')
                ->sortable()
                ->searchable(),

            Column::make(t('email'), 'email')
                ->sortable()
                ->searchable(),

            Column::make(t('active'), 'active')
                ->sortable()
                ->toggleable(hasPermission: true, trueLabel: '1', falseLabel: '0')
                ->bodyAttribute('flex mt-2 mx-3'),

            Column::make(t('created_at'), 'created_at_formatted', 'created_at')
                ->sortable(),
        ];
    }

    public function filters(): array
    {
        return [];
    }

    public function onUpdatedToggleable(string $id, string $field, string $value): void
    {
        $user = User::find($id);
        if (checkPermission('admin.users.edit')) {
            if ($user) {
                if (auth()->id() === $user->id) {
                    $this->notify([
                        'message' => t('account_cannot_be_deactivated'),
                        'type' => 'warning',
                    ]);

                    return;
                }

                if (! auth()->user()->is_admin && $user->is_admin) {
                    $this->notify([
                        'message' => t('account_cannot_be_deactivated'),
                        'type' => 'warning',
                    ]);

                    return;
                }

                if (auth()->user()->is_admin && $user->is_admin) {
                    $user->active = ($value === '1') ? 1 : 0;
                    $user->save();

                    $statusMessage = $user->active
                        ? t('user_activated_successfully')
                        : t('user_deactivated_successfully');

                    $this->notify([
                        'message' => $statusMessage,
                        'type' => 'success',
                    ]);

                    return;
                }

                $user->active = ($value === '1') ? 1 : 0;
                $user->save();

                $statusMessage = $user->active
                    ? t('user_activated_successfully')
                    : t('user_deactivated_successfully');

                $this->notify([
                    'message' => $statusMessage,
                    'type' => 'success',
                ]);
            }
        } else {
            $this->notify([
                'message' => t('no_permission_to_perform_action'),
                'type' => 'warning',
            ]);
        }
    }
}
