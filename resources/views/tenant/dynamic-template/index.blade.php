<x-app-layout>

    <x-slot:title>
        {{ t('create_template') }}
    </x-slot:title>
    <div class="mx-auto h-full">
        <div class="w-full overflow-hidden rounded-lg shadow-xs">
            <div class="w-full overflow-x-auto bg-white dark:bg-gray-800">
                <div id="dynamic-templates" class="w-full">
                    <whatsapp-template-manager></whatsapp-template-manager>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
<script>
    // Global variables for Vue components
    window.subdomain = @json($subdomain);
    window.business_account_id = @json(get_tenant_setting_from_db('whatsapp', 'wm_business_account_id'));

    // FIXED: Pass the properly formatted template data
    @if (isset($templates))
        window.templateEdit = @json($templates);
    @else
        window.templateEdit = null;
    @endif

    // Additional data that might be needed
    window.categories = @json($categories ?? []);
    window.languages = @json($languages ?? []);
</script>
