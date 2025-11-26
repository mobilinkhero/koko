<x-guest-layout>
    <div class="mx-auto px-4 py-6 bg-gray-100">
        <div x-data="{
            currentVersion: '{{ $currentVersion }}',
            requiredVersion: '{{ $requiredVersion }}',
            backupConfirmed: false,
            processing: false
        }" class="max-w-3xl mx-auto">
            <!-- Card with shadow and rounded corners, matching WhatsApp theme -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
                <!-- Header matching your purple/indigo theme -->
                <div class="bg-indigo-600 dark:bg-indigo-700 px-6 py-4">
                    <h3 class="text-lg font-medium text-center text-white flex items-center justify-center gap-2">
                        <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                        Database upgrade is required!
                    </h3>
                </div>

                <!-- Body - matching your clean layout style -->
                <div class="px-6 py-4">
                    <div class="mb-6 text-gray-700 dark:text-gray-300">
                        <p>You need to perform a database upgrade before proceeding.
                            Your files version is
                            <span class="font-semibold text-indigo-600 dark:text-indigo-400"
                                x-text="currentVersion"></span>
                            and database version is
                            <span class="font-semibold text-indigo-600 dark:text-indigo-400"
                                x-text="requiredVersion"></span>.
                        </p>
                    </div>

                    <!-- Warning Message - styled to match the theme -->
                    <div
                        class="flex items-start p-4 mb-6 rounded-lg border border-yellow-200 bg-yellow-50 dark:bg-yellow-900/20 dark:border-yellow-800/50">
                        <div class="flex-shrink-0">
                            <x-heroicon-s-exclamation-triangle class="w-5 h-5 text-yellow-500 dark:text-yellow-400" />
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-yellow-700 dark:text-yellow-300">
                                Make sure that you have a backup of your database before performing an upgrade.
                            </p>
                        </div>
                    </div>

                    <div x-data="upgradeComponent()">
                        <form @submit.prevent="submitUpgrade">
                            @csrf

                            <!-- Checkbox -->
                            <div class="flex items-center mb-6">
                                <input type="checkbox" id="confirm-backup" name="confirm-backup"
                                    x-model="backupConfirmed"
                                    class="w-4 h-4 text-indigo-600 dark:text-indigo-500 rounded border-gray-300 dark:border-gray-600 focus:ring-indigo-500">
                                <label for="confirm-backup" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                    I confirm that I have created a database backup
                                </label>
                            </div>

                            <!-- Button -->
                            <div class="text-center mt-4">
                                <button type="submit" :disabled="!backupConfirmed || processing" :class="{
                                        'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500': backupConfirmed,
                                        'bg-indigo-400 dark:bg-indigo-500/50': !backupConfirmed
                                    }"
                                    class="inline-flex items-center justify-center px-5 py-2.5 border border-transparent text-sm font-medium rounded-md text-white focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors">
                                    <span x-show="processing" class="inline-block mr-2">
                                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                    </span>
                                    <x-heroicon-o-arrow-up-tray x-show="!processing" class="-ml-1 mr-2 h-4 w-4" />
                                    <span>UPGRADE NOW</span>
                                </button>
                            </div>
                        </form>
                    </div>

                </div>

                <!-- Footer note - matching your UI style -->
                <div
                    class="px-6 py-4 bg-gray-50 dark:bg-gray-850 text-xs text-gray-500 dark:text-gray-400 italic text-center">
                    This message may show if you uploaded files from a newer version downloaded from CodeCanyon to your
                    existing installation or you used an auto-upgrade tool.
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
<script>
    function upgradeComponent() {
        return {
            backupConfirmed: false,
            processing: false,

            async submitUpgrade() {
                if (!this.backupConfirmed) return;

                this.processing = true;

                try {
                    const response = await fetch("/upgrade", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            'confirm-backup': true
                        })
                    });

                    if (!response.ok) {
                        const errorData = await response.json();
                        return;
                    }

                    const data = await response.json();
                    // Redirect based on Laravel response
                    if (data.redirect_url) {
                        window.location.assign(data.redirect_url);
                    }
                    showNotification(data.message, data.success ? 'success' : 'danger');

                } catch (error) {
                    console.warning("Upgrade Error:", error);
                } finally {
                    this.processing = false;
                }
            }

        }
    }
</script>