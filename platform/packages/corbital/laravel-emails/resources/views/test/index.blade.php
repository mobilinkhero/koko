@extends('laravel-emails::backend.layout')

@section('title', 'Test Email')

@section('content')
<div class="bg-white rounded-lg shadow-md">
    <div class="flex justify-between items-center p-4 border-b">
        <h1 class="text-xl font-semibold text-gray-800">Test Email</h1>
        <a href="{{ route('laravel-emails.templates.index') }}"
            class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition">
            Back to Templates
        </a>
    </div>

    @if (session('success'))
    <div class="bg-success-100 border-l-4 border-success-500 text-success-700 p-4 mb-4" role="alert">
        {{ session('success') }}
    </div>
    @endif

    @if (session('error'))
    <div class="bg-danger-100 border-l-4 border-danger-500 text-danger-700 p-4 mb-4" role="alert">
        {{ session('error') }}
    </div>
    @endif

    <form action="{{ route('laravel-emails.test.send') }}" method="POST" class="p-6" id="testEmailForm">
        @csrf

        <div class="mb-6">
            <label for="template_id" class="block text-sm font-medium text-gray-700 mb-1">Template</label>
            <select name="template_id" id="template_id"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50"
                required>
                <option value="">Select a template</option>
                @foreach ($templates as $template)
                <option value="{{ $template->id }}" {{ request('template')==$template->id ? 'selected' : '' }}>
                    {{ $template->name }} ({{ $template->slug }})
                </option>
                @endforeach
            </select>
            @error('template_id')
            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="to_email" class="block text-sm font-medium text-gray-700 mb-1">Recipient Email</label>
                <input type="email" name="to_email" id="to_email"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50"
                    placeholder="recipient@example.com" required>
                @error('to_email')
                <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="to_name" class="block text-sm font-medium text-gray-700 mb-1">Recipient Name
                    (optional)</label>
                <input type="text" name="to_name" id="to_name"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50"
                    placeholder="John Doe">
                @error('to_name')
                <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div id="templateVariables" class="mb-6 hidden">
            <h2 class="text-lg font-medium text-gray-800 mb-3">Template Variables</h2>
            <p class="text-sm text-gray-500 mb-3">Enter values for the template variables below:</p>
            <div id="variablesContainer" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Dynamic variables will be loaded here -->
            </div>
        </div>

        <div class="mt-6 border-t pt-6 flex justify-between items-center">
            <div class="flex items-center">
                <input type="checkbox" name="save_log" id="save_log" value="1" checked
                    class="rounded border-gray-300 text-info-600 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50">
                <label for="save_log" class="ml-2 text-sm text-gray-700">Save email log</label>
            </div>
            <button type="submit" class="px-6 py-3 bg-success-600 text-white rounded hover:bg-success-700 transition">
                Send Test Email
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
                const templateSelect = document.getElementById('template_id');
                const templateVariables = document.getElementById('templateVariables');
                const variablesContainer = document.getElementById('variablesContainer');

                // Template variable definitions (these would be loaded from your backend)
                const templates = {
                    @foreach ($templates as $template)
                        "{{ $template->id }}": {
                            name: "{{ $template->name }}",
                            variables: @json($template->variables ?? [])
                        },
                    @endforeach
                };

                // Default variables available in all templates
                const defaultVariables = ['app_name', 'app_url'];

                // Handler for when template selection changes
                templateSelect.addEventListener('change', function() {
                    const templateId = this.value;

                    // Clear previous variables
                    variablesContainer.innerHTML = '';

                    if (!templateId) {
                        templateVariables.classList.add('hidden');
                        return;
                    }

                    const template = templates[templateId];
                    if (!template) {
                        templateVariables.classList.add('hidden');
                        return;
                    }

                    // Show variables section
                    templateVariables.classList.remove('hidden');

                    // Add default variables
                    defaultVariables.forEach(varName => {
                        addVariableField(varName, varName === 'app_name' ? '{{ config('app.name') }}' :
                            '{{ config('app.url') }}');
                    });

                    // Add template-specific variables
                    if (template.variables && template.variables.length > 0) {
                        template.variables.forEach(varName => {
                            if (!defaultVariables.includes(varName)) {
                                addVariableField(varName, '');
                            }
                        });
                    }
                });

                // Function to add a variable input field
                function addVariableField(name, defaultValue = '') {
                    const field = document.createElement('div');
                    field.innerHTML = `
                    <label for="var_${name}" class="block text-sm font-medium text-gray-700 mb-1">${name}</label>
                    <input type="text" name="test_data[${name}]" id="var_${name}"
                           value="${defaultValue}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50">
                `;
                    variablesContainer.appendChild(field);
                }

                // Trigger change event if a template is already selected (e.g. from URL parameter)
                if (templateSelect.value) {
                    templateSelect.dispatchEvent(new Event('change'));
                }
            });
</script>
@endpush
@endsection