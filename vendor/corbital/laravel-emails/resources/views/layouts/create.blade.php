@extends('laravel-emails::backend.layout')

@section('title', isset($layout) ? 'Edit Email Layout' : 'Create Email Layout')

@section('content')
<div class="bg-white rounded-lg shadow-md">
    <div class="p-4 border-b">
        <h1 class="text-xl font-semibold text-gray-800">
            {{ isset($layout) ? 'Edit Email Layout: ' . $layout->name : 'Create New Email Layout' }}
        </h1>
    </div>

    <div class="p-6">
        @if ($errors->any())
        <div class="bg-danger-100 border-l-4 border-danger-500 text-danger-700 p-4 mb-4" role="alert">
            <p class="font-bold">Validation Error</p>
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form
            action="{{ isset($layout) ? route('laravel-emails.layouts.update', $layout) : route('laravel-emails.layouts.store') }}"
            method="POST">
            @csrf
            @if(isset($layout))
            @method('PUT')
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-1">
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" name="name" id="name"
                            value="{{ old('name', isset($layout) ? $layout->name : '') }}"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50"
                            required>
                    </div>

                    <div class="mb-4">
                        <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                        <input type="text" name="slug" id="slug"
                            value="{{ old('slug', isset($layout) ? $layout->slug : '') }}"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50"
                            placeholder="auto-generated-if-empty">
                        <p class="text-xs text-gray-500 mt-1">If left empty, the slug will be automatically generated
                            from the name.</p>
                    </div>

                    <div class="mb-4">
                        <label for="variables" class="block text-sm font-medium text-gray-700 mb-1">Variables
                            (comma-separated)</label>
                        <input type="text" name="variables" id="variables"
                            value="{{ old('variables', isset($layout) ? (is_array($layout->variables) ? implode(', ', $layout->variables) : '') : '') }}"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50"
                            placeholder="company_name, company_logo, etc.">
                        <p class="text-xs text-gray-500 mt-1">List variables that can be used in the header, footer and
                            master template.</p>
                    </div>

                    <div class="mb-4 flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                            class="rounded border-gray-300 text-info-600 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50"
                            {{ (old('is_active', isset($layout) ? $layout->is_active : true)) ? 'checked' : '' }}>
                        <label for="is_active" class="ml-2 block text-sm text-gray-700">Active</label>
                    </div>

                    <div class="mb-4 flex items-center">
                        <input type="checkbox" name="is_default" id="is_default" value="1"
                            class="rounded border-gray-300 text-info-600 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50"
                            {{ (old('is_default', isset($layout) ? $layout->is_default : false)) ? 'checked' : '' }}>
                        <label for="is_default" class="ml-2 block text-sm text-gray-700">Default Layout</label>
                        <p class="text-xs text-gray-500 ml-6">Setting this as default will unset any other default
                            layouts.</p>
                    </div>
                </div>

                <div class="col-span-1">
                    <!-- Design Tabs -->
                    <div class="mb-4">
                        <div class="border-b border-gray-200">
                            <nav class="-mb-px flex space-x-4">
                                <button type="button"
                                    class="design-tab py-2 px-4 text-sm font-medium border-b-2 border-info-500 text-info-600 active"
                                    data-tab="header">
                                    Header
                                </button>
                                <button type="button"
                                    class="design-tab py-2 px-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300"
                                    data-tab="footer">
                                    Footer
                                </button>
                                <button type="button"
                                    class="design-tab py-2 px-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300"
                                    data-tab="master">
                                    Master Template
                                </button>
                            </nav>
                        </div>
                    </div>

                    <!-- Header Content -->
                    <div id="header-tab" class="design-content">
                        <div class="mb-4">
                            <label for="header" class="block text-sm font-medium text-gray-700 mb-1">Header HTML</label>
                            <textarea name="header" id="header" rows="10"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50 font-mono text-sm">{{ old('header', isset($layout) ? $layout->header : '') }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">HTML for the email header. Use {HEADER} in the master
                                template to place this content.</p>
                        </div>
                    </div>

                    <!-- Footer Content -->
                    <div id="footer-tab" class="design-content hidden">
                        <div class="mb-4">
                            <label for="footer" class="block text-sm font-medium text-gray-700 mb-1">Footer HTML</label>
                            <textarea name="footer" id="footer" rows="10"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50 font-mono text-sm">{{ old('footer', isset($layout) ? $layout->footer : '') }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">HTML for the email footer. Use {FOOTER} in the master
                                template to place this content.</p>
                        </div>
                    </div>

                    <!-- Master Template Content -->
                    <div id="master-tab" class="design-content hidden">
                        <div class="mb-4">
                            <label for="master_template" class="block text-sm font-medium text-gray-700 mb-1">Master
                                Template HTML</label>
                            <textarea name="master_template" id="master_template" rows="10"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50 font-mono text-sm">{{ old('master_template', isset($layout) ? $layout->master_template : '') }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">HTML structure for the email. Use {HEADER}, {CONTENT},
                                and {FOOTER} placeholders.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 border-t pt-4">
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('laravel-emails.layouts.index') }}"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-info-600 text-white rounded hover:bg-info-700 transition">
                        {{ isset($layout) ? 'Update Layout' : 'Create Layout' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
            // Tab functionality
            const tabButtons = document.querySelectorAll('.design-tab');
            const tabContents = document.querySelectorAll('.design-content');

            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');

                    // Hide all tabs and remove active class
                    tabContents.forEach(tab => tab.classList.add('hidden'));
                    tabButtons.forEach(btn => {
                        btn.classList.remove('active', 'border-info-500', 'text-info-600');
                        btn.classList.add('border-transparent', 'text-gray-500');
                    });

                    // Show selected tab and add active class
                    document.getElementById(`${tabId}-tab`).classList.remove('hidden');
                    this.classList.add('active', 'border-info-500', 'text-info-600');
                    this.classList.remove('border-transparent', 'text-gray-500');
                });
            });

            // Auto-generate slug from name
            const nameInput = document.getElementById('name');
            const slugInput = document.getElementById('slug');

            if (nameInput && slugInput) {
                nameInput.addEventListener('blur', function() {
                    if (slugInput.value.trim() === '') {
                        // Generate slug from name
                        slugInput.value = nameInput.value
                            .toLowerCase()
                            .replace(/[^\w\s-]/g, '')
                            .replace(/[\s_-]+/g, '-')
                            .replace(/^-+|-+$/g, '');
                    }
                });
            }
        });
</script>
@endsection