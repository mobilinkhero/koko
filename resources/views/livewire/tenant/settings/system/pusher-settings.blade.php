<div>
    <x-slot:title>
        {{ t('real_time_event_broadcasting') }}
    </x-slot:title>
    <!-- Page Heading -->
    <div class="pb-6">
        <x-settings-heading>{{ t('system_settings') }}</x-settings-heading>
    </div>

    <div class="flex flex-wrap lg:flex-nowrap gap-4">
        <!-- Sidebar Menu -->
        <div class="w-full lg:w-1/5">
            <x-tenant-system-settings-navigation wire:ignore />
        </div>
        <!-- Main Content -->
        <div class="flex-1 space-y-5">
            <form wire:submit="save" class="space-y-6">
                <x-card class="rounded-lg">
                    <x-slot:header>
                        <x-settings-heading>
                            {{ t('real_time_event_broadcasting') }}
                        </x-settings-heading>
                        <x-settings-description>
                            {{ t('real_time_event_broadcasting_description') }}
                        </x-settings-description>
                    </x-slot:header>
                    <x-slot:content>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <!-- APP ID -->
                            <div>
                                <x-label for="app_id" :value="t('app_id')" />
                                <x-input wire:model.defer="app_id" id="app_id" type="text" class="mt-1" />
                                <x-input-error for="app_id" class="mt-2" />
                            </div>

                            <!-- APP Key -->
                            <div>
                                <x-label for="app_key" :value="t('app_key')" />
                                <x-input wire:model.defer="app_key" id="app_key" type="text" class="mt-1" />
                                <x-input-error for="app_key" class="mt-2" />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <!-- APP Secret -->
                            <div class="mt-2">
                                <x-label for="app_secret" :value="t('app_secret')" />
                                <x-input wire:model.defer="app_secret" id="app_secret" type="text" class="mt-1" />
                                <x-input-error for="app_secret" class="mt-2" />
                            </div>

                            <!-- Link Text -->
                            <div>
                                <x-label for="cluster"
                                    class="flex items-center justify-between space-x-1 sm:mt-px sm:pt-2">
                                    <div class="flex items-center flex-wrap space-x-1">
                                        <span data-tippy-content="{{ t('leave_blank_for_default_cluster') }}">
                                            <x-heroicon-o-question-mark-circle
                                                class="w-5 h-5 text-slate-500 dark:text-slate-400" />
                                        </span>
                                        <span class="font-medium text-sm text-slate-700 dark:text-slate-200">{{
                                            t('cluster') }}</span>
                                        <a href="https://pusher.com/docs/clusters" target="_blank"
                                            class="text-info-500"> {{ t('pusher_link') }} </a>
                                    </div>
                                </x-label>
                                <x-input wire:model.defer="cluster" id="cluster" type="text" class="mt-1" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 mt-2">
                            <!-- Link Text -->
                            <div x-data="{ real_time_notify: @entangle('real_time_notify').defer }">
                                <x-label for="real_time_notify"
                                    class="flex items-center justify-between space-x-1 sm:mt-px sm:pt-2">
                                    <div class="flex items-center flex-wrap space-x-1">
                                        <span class="font-medium text-sm text-slate-700 dark:text-slate-200">
                                            {{ t('enable_real_time_notifications') }}
                                        </span>
                                    </div>
                                </x-label>

                                <div>
                                    <x-toggle id="real_time_notify" name="real_time_notify" :value="$real_time_notify"
                                        wire:model="real_time_notify" />
                                </div>
                            </div>


                            <div x-data="{ desk_notify: @entangle('desk_notify').defer }">
                                <x-label for="desk_notify"
                                    class="flex items-center justify-between space-x-1 sm:mt-px sm:pt-2">
                                    <div class="flex items-center flex-wrap space-x-1">
                                        <span data-tippy-content="{{ t('dest_notify_desc') }}">
                                            <x-heroicon-o-question-mark-circle
                                                class="w-5 h-5 text-slate-500 dark:text-slate-400" />
                                        </span>
                                        <span class="font-medium text-sm text-slate-700 dark:text-slate-200">
                                            {{ t('enable_desktop_notifications') }}
                                        </span>
                                    </div>
                                </x-label>

                                <div>
                                    <x-toggle id="desk_notify" name="desk_notify" :value="$desk_notify"
                                        wire:model="desk_notify" />
                                </div>
                            </div>

                        </div>
                        <div class="mt-2">
                            <x-label for="dismiss_desk_notification"
                                class="flex items-center justify-between space-x-1 sm:mt-px sm:pt-2">
                                <div>
                                    <span data-tippy-content="{{ t('google_chrome') }}">
                                        <x-heroicon-o-question-mark-circle
                                            class="w-5 h-5 text-slate-500 dark:text-slate-400 inline-flex" />
                                    </span>
                                    <span class="font-medium text-sm text-slate-700 dark:text-slate-200">{{
                                        t('auto_dismiss_desktop') }}</span>
                                </div>
                            </x-label>
                            <x-input wire:model.defer="dismiss_desk_notification" id="dismiss_desk_notification"
                                type="number" class="mt-1" />
                        </div>
                    </x-slot:content>

                    <!-- Submit Button -->
                    @if (checkPermission('tenant.system_settings.edit'))
                    <x-slot:footer class="bg-slate-50 dark:bg-transparent rounded-b-lg">
                        <div class="flex justify-end space-x-2">
                            <!--Button will only get displayed when all fields are filled -->
                            @if (!empty($app_id) && !empty($app_key) && !empty($app_secret) && !empty($cluster))
                            <x-button.loading-button wire:click="testConnection">
                                {{ t('test_pusher') }}
                            </x-button.loading-button>
                            @endif
                            <x-button.loading-button type="submit" target="save">
                                {{ t('save_changes') }}
                            </x-button.loading-button>
                        </div>
                    </x-slot:footer>
                    @endif
                </x-card>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', () => {
            Alpine.store('echoManager').init();
            Alpine.store('pusherManager').init();

            //Listen to a test channel/event
            if (Alpine.store('echoManager').echo) {
                Alpine.store('echoManager').echo
                    .channel('whatsmark-test-channel')
                    .listen('.whatsmark-test-event', (data) => {
                        Alpine.store('pusherManager').showDesktopNotification(data.title, {
                            message: data.message || 'You have a new event notification',
                        });
                    });
            }
        });
</script>
@endpush
