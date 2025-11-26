@extends('laravel-emails::backend.layout')

@section('title', 'Edit Email Template')

@section('content')
<div class="bg-white rounded-lg shadow-md">
    <div class="flex justify-between items-center p-4 border-b">
        <h1 class="text-xl font-semibold text-gray-800">Edit Email Template</h1>
        <div class="flex space-x-2">
            <a href="{{ route('laravel-emails.templates.index') }}"
                class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition">
                Back to Templates
            </a>
            <a href="{{ route('laravel-emails.test.index') }}?template={{ $template->id }}"
                class="px-4 py-2 bg-success-600 text-white rounded hover:bg-success-700 transition">
                Test Template
            </a>
        </div>
    </div>

    <form action="{{ route('laravel-emails.templates.update', $template) }}" method="POST" class="p-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Template Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $template->name) }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50 @error('name') border-danger-300 @enderror"
                        required>
                    @error('name')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug', $template->slug) }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50 @error('slug') border-danger-300 @enderror"
                        {{ $template->is_system ? 'readonly' : '' }}>
                    <p class="mt-1 text-xs text-gray-500">
                        @if($template->is_system)
                        System template slugs cannot be changed.
                        @else
                        Unique identifier used in code.
                        @endif
                    </p>
                    @error('slug')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Email Subject</label>
                    <input type="text" name="subject" id="subject" value="{{ old('subject', $template->subject) }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50 @error('subject') border-danger-300 @enderror"
                        required>
                    <p class="mt-1 text-xs text-gray-500">You can use variables like @{{ app_name }}</p>
                    @error('subject')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category
                        (optional)</label>
                    <input type="text" name="category" id="category" value="{{ old('category', $template->category) }}"
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
                        <option value="{{ $layout->id }}" {{ (int)old('layout_id', $template->layout_id) === $layout->id
                            ? 'selected' : '' }}>
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
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50 @error('description') border-danger-300 @enderror">{{ old('description', $template->description) }}</textarea>
                    @error('description')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="variables" class="block text-sm font-medium text-gray-700 mb-1">Template Variables (one
                        per line)</label>
                    <textarea name="variables" id="variables" rows="5"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50 @error('variables') border-danger-300 @enderror">{{ old('variables', $variablesString ?? '') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">Example: name<br>email<br>verification_url</p>
                    @error('variables')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ $template->is_active ? 'checked' : '' }}
                        class="rounded border-gray-300 text-info-600 shadow-sm focus:border-info-300 focus:ring
                        focus:ring-info-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700">Active</span>
                    </label>
                </div>

                @if($template->is_system)
                <div class="mb-6 bg-info-50 p-4 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-info-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-info-800">System Template</h3>
                            <div class="mt-2 text-sm text-info-700">
                                <p>This is a system template used for core functionality. It cannot be deleted, but you
                                    can modify its content and subject.</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <div>
                <div class="mb-6">
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-1">HTML Content</label>
                    <div class="border border-gray-300 rounded-md" style="min-height: 400px;">
                        <textarea name="content" id="content" rows="20"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50 @error('content') border-danger-300 @enderror">{{ old('content', $template->content) }}</textarea>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Use @{{ variable_name }} syntax for variables</p>
                    @error('content')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6 mt-8">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Email Preview</h3>
                    <div class="bg-gray-50 p-3 rounded border border-gray-300 mb-2 flex justify-between items-center">
                        <span class="text-sm text-gray-600">Preview with sample data</span>
                        <button type="button" id="previewButton"
                            class="px-3 py-1 bg-info-600 text-white text-sm rounded hover:bg-info-700 transition">
                            Preview
                        </button>
                    </div>
                    <div id="emailPreview" class="hidden">
                        <div class="bg-white p-4 rounded border border-gray-300 mb-3">
                            <div class="mb-2 pb-2 border-b">
                                <strong class="text-sm text-gray-700">Subject:</strong>
                                <div id="previewSubject" class="text-sm"></div>
                            </div>
                            <div>
                                <strong class="text-sm text-gray-700">Content:</strong>
                                <div id="previewContent" class="mt-2 text-sm border p-3 rounded bg-gray-50"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 border-t pt-6">
            <button type="submit" class="px-6 py-3 bg-info-600 text-white rounded hover:bg-info-700 transition">
                Update Template
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
                // Preview functionality
                const previewButton = document.getElementById('previewButton');
                const previewSubject = document.getElementById('previewSubject');
                const previewContent = document.getElementById('previewContent');
                const emailPreview = document.getElementById('emailPreview');
                const templateIdInput = document.getElementById('slug');
                const contentInput = document.getElementById('content');
                const subjectInput = document.getElementById('subject');

                if (previewButton) {
                    previewButton.addEventListener('click', function() {
                        // Show the preview container
                        emailPreview.classList.remove('hidden');

                        // Get the current template ID and content
                        const templateId = templateIdInput.value;
                        const content = contentInput.value;
                        const subject = subjectInput.value;

                        // Use sample data
                        const sampleData = {
                            app_name: '{{ config('app.name') }}',
                            app_url: '{{ config('app.url') }}',
                            name: 'John Doe',
                            email: 'john@example.com',
                            // Add more sample data based on variables
                        };

                        // Basic variable replacement for preview
                        let previewSubjectText = subject;
                        let previewContentHtml = content;

                        // Process variables - use safer approach for regex with Blade templates
                        Object.keys(sampleData).forEach(function(key) {
                            let search = new RegExp('{{\\s*' + key + '\\s*}}', 'g');
                            previewSubjectText = previewSubjectText.replace(search, sampleData[key]);
                            previewContentHtml = previewContentHtml.replace(search, sampleData[key]);
                        });

                        // Update preview
                        previewSubject.textContent = previewSubjectText;
                        previewContent.innerHTML = previewContentHtml;
                    });
                }

                // Initialize a code editor if needed
                // For a simple approach, you can use a textarea
                // For a more advanced approach, you can integrate a code editor like CodeMirror or TinyMCE
            });
</script>
@endpush
@endsection