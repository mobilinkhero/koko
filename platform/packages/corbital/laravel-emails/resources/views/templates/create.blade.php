@extends('laravel-emails::backend.layout')

@section('title', 'Create Email Template')

@section('content')
<div class="bg-white rounded-lg shadow-md">
    <div class="flex justify-between items-center p-4 border-b">
        <h1 class="text-xl font-semibold text-gray-800">Create Email Template</h1>
        <a href="{{ route('laravel-emails.templates.index') }}"
            class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition">
            Back to Templates
        </a>
    </div>

    <form action="{{ route('laravel-emails.templates.store') }}" method="POST" class="p-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Template Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50 @error('name') border-danger-300 @enderror"
                        required>
                    @error('name')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug (optional)</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug') }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50 @error('slug') border-danger-300 @enderror">
                    <p class="mt-1 text-xs text-gray-500">Leave empty to auto-generate from name</p>
                    @error('slug')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Email Subject</label>
                    <input type="text" name="subject" id="subject" value="{{ old('subject') }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50 @error('subject') border-danger-300 @enderror"
                        required>
                    <p class="mt-1 text-xs text-gray-500">You can use variables like {{ env('APP_NAME') }}</p>
                    @error('subject')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-6">
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category
                        (optional)</label>
                    <input type="text" name="category" id="category" value="{{ old('category') }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50 @error('category') border-danger-300 @enderror">
                    @error('category')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="layout_id" class="block text-sm font-medium text-gray-700 mb-1">Email Layout</label>
                    <select name="layout_id" id="layout_id"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50 @error('layout_id') border-danger-300 @enderror">
                        <option value="">-- Select a Layout --</option>
                        @foreach(\Corbital\LaravelEmails\Models\EmailLayout::where('is_active',
                        true)->orderBy('name')->get() as $layout)
                        <option value="{{ $layout->id }}" {{ (int)old('layout_id')===$layout->id ? 'selected' : '' }}>
                            {{ $layout->name }}
                            @if($layout->is_default) (Default) @endif
                        </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">The layout provides consistent header and footer for your
                        email</p>
                    @error('layout_id')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description
                        (optional)</label>
                    <textarea name="description" id="description" rows="3"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50 @error('description') border-danger-300 @enderror">{{ old('description') }}</textarea>
                    @error('description')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="variables" class="block text-sm font-medium text-gray-700 mb-1">Template Variables (one
                        per line)</label>
                    <textarea name="variables" id="variables" rows="5"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50 @error('variables') border-danger-300 @enderror">{{ old('variables') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">Example: name<br>email<br>verification_url</p>
                    @error('variables')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" checked
                            class="rounded border-gray-300 text-info-600 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700">Active</span>
                    </label>
                </div>
            </div>

            <div>
                <div class="mb-6">
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-1">HTML Content</label>
                    <div class="border border-gray-300 rounded-md" style="min-height: 400px;">
                        <textarea name="content" id="content" rows="20"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50 @error('content') border-danger-300 @enderror">{{ old('content') }}</textarea>
                    </div>
                    {{-- <p class="mt-1 text-xs text-gray-500">Use {{variable_name}} syntax for variables</p> --}}
                    @error('content')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6 mt-8">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Variable Preview</h3>
                    <div class="bg-gray-50 p-3 rounded border border-gray-300">
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li><span class="font-semibold">Default Variables:</span></li>
                            <li>{{env('APP_NAME')}} - Application name</li>
                            <li>{{env('APP_URL')}} - Application URL</li>
                            <li><span class="font-semibold mt-2 block">Custom Variables:</span></li>
                            <li>Add your custom variables in the field above</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 border-t pt-6">
            <button type="submit" class="px-6 py-3 bg-info-600 text-white rounded hover:bg-info-700 transition">
                {{ t('create_template') }}
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
                // Simple slug generator
                const nameInput = document.getElementById('name');
                const slugInput = document.getElementById('slug');

                if (nameInput && slugInput) {
                    nameInput.addEventListener('blur', function() {
                        if (!slugInput.value) {
                            slugInput.value = nameInput.value
                                .toLowerCase()
                                .replace(/[^\w\s-]/g, '')
                                .replace(/[\s_-]+/g, '-')
                                .replace(/^-+|-+$/g, '');
                        }
                    });
                }

                // Initialize a code editor if needed
                // For a simple approach, you can use a textarea
                // For a more advanced approach, you can integrate a code editor like CodeMirror or TinyMCE
            });
</script>
@endpush
@endsection