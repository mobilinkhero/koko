<div class="flex justify-center items-center min-h-[calc(100vh-170px)] px-4 sm:px-6 lg:px-8 py-6">
    <div
        class="w-full max-w-sm sm:max-w-md md:max-w-lg lg:max-w-md xl:max-w-lg bg-white dark:bg-slate-700 rounded-xl shadow-lg overflow-hidden">
        <div class="bg-danger-50 dark:bg-danger-900/20 p-6 flex items-center justify-center">
            <div class="h-16 w-16 rounded-full bg-danger-100 dark:bg-danger-800 flex items-center justify-center">
                <x-heroicon-o-exclamation-circle class="h-8 w-8 text-danger-500 dark:text-danger-400" />
            </div>
        </div>

        <div class="p-6">
            <h2 class="text-xl sm:text-2xl font-bold text-center text-gray-900 dark:text-white mb-4">
                {{ t('your_account_is_discconected') }}
            </h2>
            <p class="text-gray-600 dark:text-gray-300 text-center mb-6 text-sm sm:text-base">
                {{ t('disconnected_info') }}
            </p>

            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                <a href="{{ tenant_route('tenant.connect') }}" target="_blank" rel="noopener noreferrer"
                    class="flex-1 bg-info-600 hover:bg-info-700 text-white font-medium py-2 px-4 rounded-lg transition-colors text-center flex items-center justify-center">
                    <x-heroicon-o-arrow-path class="h-5 w-5 mr-2" />
                    {{ t('connect_account') }}
                </a>
                <a href="{{ tenant_route('tenant.tickets.create') }}" target="_blank" rel="noopener noreferrer"
                    class="bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-800 dark:text-gray-200 font-medium py-2 px-4 rounded-lg transition-colors flex items-center justify-center">
                    <x-heroicon-o-chat-bubble-left-right class="h-5 w-5 mr-2" />
                    {{ t('support') }}
                </a>
            </div>
        </div>
    </div>
</div>