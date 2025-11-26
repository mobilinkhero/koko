<x-app-layout>
    <x-slot name="title">{{ t('theme_style_settings') }}</x-slot>

    <div class="mx-auto">
        <div class="pb-3 font-display">
            <x-settings-heading>{{ t('theme_style') }}
            </x-settings-heading>
        </div>
        <div class="flex flex-wrap lg:flex-nowrap gap-4">
            <!-- Sidebar Menu -->
            <div class="w-full lg:w-1/5">
                <x-admin-system-settings-navigation wire:ignore />
            </div>
            <!-- Main Content -->
            <div class="flex-1 space-y-5">
                <div id="theme-style-app">
                    <theme-style-settings initial-theme="{{ $currentTheme }}"
                        save-url="{{ route('admin.theme-style.save') }}" @theme-saved="onThemeSaved">
                    </theme-style-settings>
                </div>
            </div>
        </div>
        <script>
            window.addEventListener('theme-saved', function() {
                // Show success message
                // Hard reload of the CSS by removing and re-adding
                const styleLink = document.getElementById('theme-style-css');
                if (styleLink) {
                    // Remove the old style
                    styleLink.remove();
                }

                // Force browser to get fresh CSS by adding timestamp parameter
                const timestamp = new Date().getTime();
                const newLink = document.createElement('link');
                newLink.id = 'theme-style-css';
                newLink.rel = 'stylesheet';
                newLink.href = "{{ route('theme-style-css') }}?" + timestamp;
                document.head.appendChild(newLink);

                // Also force refresh other components that might be caching styles
                document.body.classList.add('theme-updated');
                setTimeout(() => {
                    document.body.classList.remove('theme-updated');
                }, 100);

                // Show notification using Alpine.js event
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: {
                        message: '{{ t('theme_style_saved') }}',
                        type: 'success'
                    }
                }));
            });
        </script>

</x-app-layout>