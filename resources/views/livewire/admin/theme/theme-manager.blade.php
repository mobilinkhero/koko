<div>
    <x-slot:title>
        {{ t('theme_manager') }}
    </x-slot:title>

    <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => route('admin.dashboard')],
        ['label' => t('theme_manager')],
    ]" />
    <div class="flex flex-col md:flex-row justify-between items-center">
        <x-settings-heading>
            {{ t('manage_theme') }}
        </x-settings-heading>
       
    </div>
    <!-- No Themes Message -->
    @if (empty($themes) || count($themes) === 0)
    <div
        class="mb-6 p-4 flex justify-start items-center gap-2 bg-info-50 dark:bg-info-800/20 border-l-4 border-info-500 dark:border-info-500 text-info-700 dark:text-info-400 rounded-md">
        <x-heroicon-o-paint-brush class="w-5 h-5 text-info-400 dark:text-info-500" />
        <p class=" text-slate-600 dark:text-slate-400 text-base">
            {{ t(key: 'no_themes_found') }}
        </p>
    </div>
    @endif

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        <!-- Upload Theme Card -->
        <!-- Theme Cards -->

        @foreach ($themes as $theme)
        <div
            class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden hover:shadow-md transition duration-200">
            <!-- Theme Image -->
            <div class="relative w-full h-48 md:h-56 lg:h-64 rounded-xl overflow-hidden shadow-sm">
                <img src="{{ asset('storage/' . $theme->theme_url) }}" alt="{{ $theme->name }}"
                    class="w-full h-full object-cover transform hover:scale-105 transition duration-500 ease-in-out"
                    onerror="this.src='{{ asset('img/img-placeholder.png') }}'; this.onerror='';">
            </div>

            <!-- Theme Details -->
            <div class="flex items-center justify-between p-4 border-t border-slate-200 dark:border-slate-700">
                <div class="flex flex-col">
                    <h4 class="font-medium text-slate-800 dark:text-slate-200">{{ $theme->name }}</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        @if ($theme->version)
                        {{ t('version') }} {{ $theme->version }}
                        @endif
                    </p>
                </div>

            </div>
            <!-- Theme Status / Activation Button and Delete Button aligned -->
            <div class="p-4 pt-0">
                <div class="flex items-center justify-between space-x-3">
                    <div class="flex-1">
                        @if ($theme->active)
                        <div disabled
                            class="flex justify-center items-center px-3 py-2 space-x-1.5 w-full text-sm font-medium text-slate-500 bg-slate-200 dark:bg-slate-700 dark:text-slate-400 rounded-md opacity-70 cursor-not-allowed">
                            <x-heroicon-s-check-circle class="w-5 h-5 text-white" />
                            <span>{{ t('active_theme') }}</span>
                        </div>
                        @else
                        <button wire:click="activate('{{ $theme->folder }}')"
                            class="flex justify-center items-center px-3 py-2 space-x-1.5 w-full text-sm font-medium text-primary-600 rounded-md border border-primary-200 dark:border-slate-600 dark:text-primary-400 hover:text-white hover:bg-primary-600 hover:border-primary-600 dark:hover:bg-primary-600 dark:hover:border-primary-600 dark:hover:text-white transition-all duration-200">
                            <x-heroicon-o-bolt class="w-5 h-5" />
                            <span>{{ t('activate_theme') }}</span>
                        </button>
                        @endif
                    </div>

                    <div class="flex-shrink-0">
                        {{-- @if(!($theme->name === 'thecore' && $theme->active == 1))
                        <button wire:click="confirmDelete({{ $theme->id }})"
                            class="flex items-center justify-center px-4 py-2 text-sm font-medium text-danger-600 hover:bg-danger-50 dark:text-danger-400 dark:hover:bg-gray-700 transition-colors rounded">
                            <x-heroicon-o-trash class="w-4 h-4 mr-1" />
                            {{ t('delete') }}
                        </button>
                        @endif --}}
                    </div>

                </div>
            </div>
        </div>
        @endforeach
    </div>
    <!-- Delete Confirmation Modal -->
    <x-modal.confirm-box :maxWidth="'lg'" :id="'delete-theme-modal'" title="{{ t('delete_theme_title') }}"
        wire:model.defer="confirmingDeletion" description="{{ t('delete_message') }} ">
        <div
            class="border-neutral-500/30 flex justify-end items-center sm:block space-x-3 bg-gray-100 dark:bg-gray-700 ">
            <x-button.cancel-button wire:click="$set('confirmingDeletion', false)">
                {{ t('cancel') }}
            </x-button.cancel-button>
            <x-button.delete-button wire:click="delete" wire:loading.attr="disabled" class="mt-3 sm:mt-0">
                {{ t('delete') }}
            </x-button.delete-button>
        </div>
    </x-modal.confirm-box>
</div>