<div class="mx-auto">
    <x-slot:title>
        {{ t('privacy_policy') }}
    </x-slot:title>
    <!-- Page Heading -->
    <div class="pb-6">
        <x-settings-heading>{{ t('system_settings') }}</x-settings-heading>
    </div>

    <div class="flex flex-wrap lg:flex-nowrap gap-4">
        <!-- Sidebar Menu -->
        <div class="w-full lg:w-1/5">
            <x-admin-system-settings-navigation wire:ignore />
        </div>
        <!-- Main Content -->
        <div class="flex-1 space-y-5">
            <form wire:submit="save" class="space-y-6">
                <x-card class="rounded-lg">
                    <x-slot:header>
                        <x-settings-heading>
                            {{ t('privacy_policy') }}
                        </x-settings-heading>
                        <x-settings-description>
                            {{ t('privacy_policy_description') }}
                        </x-settings-description>
                    </x-slot:header>
                    <x-slot:content>
                        <!-- Title Field -->
                        <div class="grid grid-cols-1 gap-y-6">
                            <div>
                                <x-label for="title" :value="t('title')" />
                                <x-input wire:model="title" id="title" type="text" class="mt-1" />
                                <x-input-error for="title" class="mt-2" />
                            </div>

                            <!-- Content Field -->
                            <div>
                                <div class="flex item-centar justify-start gap-1 mb-2">
                                    <x-label for="content" :value="t('content')" />
                                </div>
                                <div wire:ignore>
                                    <div x-data x-ref="editor" x-init="const quill = new Quill($refs.editor, { theme: 'snow' });

                                    let timeout = null; // Declare timeout for debouncing

                                    quill.on('text-change', () => {
                                        clearTimeout(timeout);
                                        timeout = setTimeout(() => {
                                            $wire.set('content', quill.root.innerHTML);
                                        }, 500); // Debounce for 500ms
                                    });

                                    $watch('$wire.content', (value) => {
                                        if (quill.root.innerHTML !== value) {
                                            quill.root.innerHTML = value || ''; // Update Quill content
                                        }
                                    });" class="rounded-b-md">
                                        {!! $content !!}
                                    </div>
                                </div>
                                <x-input-error for="content" class="mt-2" />

                            </div>
                        </div>
                    </x-slot:content>

                    <!-- Submit Button -->
                    @if (checkPermission('admin.system_settings.edit'))
                    <x-slot:footer class="bg-slate-50 dark:bg-transparent rounded-b-lg">
                        <div class="flex justify-end">
                            <x-button.loading-button type="submit" target="save">
                                {{ t('save_changes_button') }}
                            </x-button.loading-button>
                        </div>
                    </x-slot:footer>
                    @endif
                </x-card>
            </form>
        </div>
    </div>
</div>
