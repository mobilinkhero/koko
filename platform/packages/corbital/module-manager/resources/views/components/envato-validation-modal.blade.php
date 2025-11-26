<!-- Envato Validation Modal -->
<div x-data="{ show: @entangle('showEnvatoModal').live }"
     x-show="show"
     x-cloak
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto">

    <!-- Background overlay -->
    <div class="fixed inset-0 bg-black bg-opacity-50"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-75"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-75"
         x-transition:leave-end="opacity-0"
         @click="$wire.closeEnvatoModal()"></div>

    <!-- Modal content -->
    <div class="flex items-center justify-center min-h-screen p-4">
        <div x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
             class="bg-white dark:bg-secondary-800 rounded-lg shadow-xl max-w-2xl w-full mx-4 relative">
            <!-- Modal header -->
            <div class="px-6 py-4 border-b border-secondary-200 dark:border-secondary-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-secondary-900">
                        Module Activation
                    </h3>
                    <button @click="$wire.closeEnvatoModal()"
                        class="text-secondary-400 hover:text-secondary-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal body -->
            <div>
                <div class="px-6 py-4">
                    <div class="flex items-center p-4 bg-primary-50 rounded-lg">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-primary-800">
                                To activate the <strong>{{ $moduleToActivate }}</strong> module, please provide your Envato cdangerentials for verification.
                            </p>
                        </div>
                    </div>
                </div>

                @if($envatoResponse)
                <div class="px-6 mb-4">
                    <div class="flex items-center p-4 bg-danger-50 rounded-lg">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-exclamation-triangle
                                class="w-5 h-5 text-danger-600 " />
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-danger-700" >
                                {{ $envatoResponse['message'] }}
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                <form wire:submit.prevent="validateAndActivateModule">
                <div class="px-6">
                    <!-- Envato Username -->
                    <div class="mb-4">
                        <label for="envato_username" class="block text-sm font-medium text-secondary-700 mb-2">
                            Envato Username
                        </label>
                        <input type="text"
                               id="envato_username"
                               wire:model="envatoUsername"
                               class="w-full px-3 py-2 border @error('envatoUsername') border-danger-300 @else border-secondary-300 @enderror rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                               placeholder="Enter your Envato username" autocomplete="off">
                        @error('envatoUsername')
                            <p class="mt-1 text-sm text-danger-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Purchase Code -->
                    <div class="mb-6">
                        <label for="envato_purchase_code" class="block text-sm font-medium text-secondary-700 mb-2">
                            Purchase Code
                        </label>
                        <input type="text"
                               id="envato_purchase_code"
                               wire:model="envatoPurchaseCode"
                               class="w-full px-3 py-2 border @error('envatoPurchaseCode') border-danger-300 @else border-secondary-300 @enderror rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                               placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" autocomplete="off">
                            <p class="mt-1 text-xs text-secondary-500">
                                You can find your purchase code in your Envato account downloads section.
                            </p>
                            @error('envatoPurchaseCode')
                                <p class="mt-1 text-sm text-danger-600" role="alert">{{ $message }}</p>
                            @enderror
                    </div>
                </div>
                    <!-- Modal footer -->
                    <div class="flex justify-end space-x-3 px-6 py-4 border-t border-secondary-200 dark:border-secondary-700">
                        <button type="button"
                                @click="$wire.closeEnvatoModal()"
                                class="px-4 py-2 text-sm font-medium text-secondary-700 bg-secondary-100 rounded-md hover:bg-secondary-200 focus:outline-none focus:ring-2 focus:ring-secondary-500 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                wire:loading.attr="disabled"
                                wire:target="validateAndActivateModule"
                                class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 disabled:opacity-50 transition-colors">
                            <span wire:loading.remove wire:target="validateAndActivateModule">
                                Verify & Activate
                            </span>
                            <span wire:loading wire:target="validateAndActivateModule">
                                Verifying...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
