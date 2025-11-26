<div class="group relative inline-block min-h-[40px]">
    <button class="dark:text-gray-200 text-primary-600 dark:hover:text-primary-400"
        onclick="Livewire.dispatch('viewContact', { contactId: {{ $id }} })">{{ $fullName }}</button>

    <!-- Action Links -->
    <div
        class="absolute left-0 top-3 mt-2 pt-1 hidden contact-actions space-x-1 text-xs text-gray-600 dark:text-gray-300">
        @if (checkPermission('tenant.contact.view'))
        <button onclick="Livewire.dispatch('viewContact', { contactId: {{ $id }} })" class="hover:text-info-600">{{
            t('view') }}</button>
        @endif

        @if (checkPermission('tenant.contact.edit'))
        <span>|</span>
        <button onclick="Livewire.dispatch('editContact', { contactId: {{ $id }} })" class="hover:text-success-600">{{
            t('edit') }}</button>
        @endif

        @if (checkPermission('tenant.contact.delete'))
        <span>|</span>
        <button onclick="Livewire.dispatch('confirmDelete', { contactId: {{ $id }} })" class="hover:text-danger-600">{{
            t('delete') }}</button>
        @endif

    </div>
</div>