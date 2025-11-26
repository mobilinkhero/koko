<div >
    <x-slot:title>
        {{ t('add_role_title') }}
    </x-slot:title>

    <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => route('admin.dashboard')],
        ['label' => t('role'), 'route' => route('admin.roles.list')],
        ['label' => $role->exists ? t('edit_role_title') : t('add_role_title')]
]" />

    <form wire:submit="save">
       
        <div class="flex flex-col lg:flex-row gap-6 mb-20">
            <div class="w-full lg:w-8/12">
                <x-card class="rounded-lg shadow-sm">
                    <x-slot:content>
                        <!-- Role Name -->
                        <div class="mb-6">
                            <div class="flex items-center gap-1">
                                <span class="text-danger-500">*</span>
                                <x-label for="name" class="font-medium">
                                    {{ t('role') }}
                                </x-label>
                            </div>
                            <x-input wire:model.defer="name" type="text" id="name"
                                placeholder="{{ t('enter_role_name') }}" class="mt-1 block w-full" autocomplete="off" />
                            <x-input-error for="name" class="mt-1" />
                        </div>

                        <!-- Permissions Table -->
                        <x-input-error for="selectedPermissions" class="mb-4" />
                        <div class="border rounded-lg border-gray-200 dark:border-gray-700 overflow-hidden ">


                            <div class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                                <div class="flex flex-col sm:grid sm:grid-cols-12 sm:gap-0">
                                    <div
                                        class="p-4 font-medium text-gray-700 dark:text-gray-300 flex items-center border-b sm:border-b-0 sm:border-r sm:col-span-4 border-gray-200 dark:border-gray-700">
                                        <x-heroicon-o-puzzle-piece
                                            class="w-5 h-5 mr-2 text-primary-600 dark:text-primary-400 flex-shrink-0" />
                                        {{ t('features') }}
                                    </div>
                                    <div
                                        class="p-4 font-medium text-gray-700 dark:text-gray-300 flex items-center sm:col-span-8">
                                        <x-heroicon-o-key
                                            class="w-5 h-5 mr-2 text-primary-600 dark:text-primary-400 flex-shrink-0" />
                                        {{ t('capabilities') }}
                                    </div>
                                </div>
                            </div>

                            <!-- Table Body -->
                            <div class="max-h-[500px] overflow-y-auto">
                                @foreach ($permissionGroups as $group => $permissions)
                                    <div
                                        class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors duration-150">
                                        <div class="grid grid-cols-12 gap-0">
                                            <!-- Module Name -->
                                            <div
                                                class="col-span-12 sm:col-span-4 p-4 text-gray-700 dark:text-gray-300 font-medium border-b sm:border-b-0 sm:border-r border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                                                {{ Str::of($group)->replace('_', ' ')->ucfirst() }}
                                            </div>

                                            <!-- Permissions -->
                                            <div class="col-span-12 sm:col-span-8 p-4">
                                                <div class="flex flex-wrap gap-3">
                                                    @foreach ($permissions as $permission)
                                                        <label
                                                            class="flex items-center p-2 bg-white dark:bg-gray-700 rounded-md border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-150 cursor-pointer">
                                                            <x-checkbox id="permission_{{ $permission['id'] }}"
                                                                value="{{ $permission['id'] }}"
                                                                wire:model.live="selectedPermissions"
                                                                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded" />
                                                            <span for="permission_{{ $permission['id'] }}"
                                                                class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                                                {{ ucfirst(str_replace('_', ' ', Str::afterLast($permission['name'], '.'))) }}
                                                            </span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </x-slot:content>
                </x-card>
            </div>

            <!-- Right Column - Users with this role -->
            <div class="w-full lg:w-4/12">
                <x-card class="rounded-lg shadow-sm">
                    <x-slot:header>
                        <div class="flex items-center">
                            <x-heroicon-o-users class="w-6 h-6 mr-2 text-primary-600 dark:text-primary-400" />
                            <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ t('users_using_this_role') }}
                            </h2>
                        </div>
                    </x-slot:header>

                    <x-slot:content>
                        <div wire:poll.30s="refreshTable">
                            <livewire:admin.tables.role-assignee-table :role_id="$role->id" />
                        </div>
                    </x-slot:content>
                </x-card>
            </div>
            <!-- Footer Actions Bar -->
            <div
                class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600 z-10">
                <div class="flex justify-end px-6 py-3">
                    <x-button.secondary class="mx-2" wire:click="cancel">
                        {{ t('cancel') }}
                    </x-button.secondary>
                    <x-button.loading-button type="submit" target="save">
                        {{ $role->exists ? t('update_button') : t('add_button') }}
                    </x-button.loading-button>
                </div>
            </div>
        </div>
    </form>
</div>
