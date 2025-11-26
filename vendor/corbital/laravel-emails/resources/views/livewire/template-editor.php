<div>
    <form wire:submit.prevent="save" class="space-y-6">
        @if (session()->has('success'))
        <div class="bg-success-100 border-l-4 border-success-500 text-success-700 p-4 mb-4" role="alert">
            {{ session('success') }}
        </div>
        @endif

        @if (session()->has('error'))
        <div class="bg-danger-100 border-l-4 border-danger-500 text-danger-700 p-4 mb-4" role="alert">
            {{ session('error') }}
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-6">
                <!-- Basic Template Information -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Template Name</label>
                    <input type="text" id="name" wire:model.blur="form.name"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50 @error('form.name') border-danger-300 @enderror"
                        required>
                    @error('form.name')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                    <input type="text" id="slug" wire:model="form.slug"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50 @error('form.slug') border-danger-300 @enderror"
                        {{ $template->is_system ? 'readonly' : '' }}>
                    <p class="mt-1 text-xs text-gray-500">
                        @if($template->is_system)
                        System template slugs cannot be changed.
                        @else
                        Unique identifier used in code. Leave empty to auto-generate from name.
                        @endif
                    </p>
                    @error('form.slug')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Email Subject</label>
                    <input type="text" id="subject" wire:model.blur="form.subject"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50 @error('form.subject') border-danger-300 @enderror"
                        required>
                    <p class="mt-1 text-xs text-gray-500">You can use variables like {{app_name}}</p>
                    @error('form.subject')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category (optional)</label>
                    <input type="text" id="category" wire:model="form.category"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50 @error('form.category') border-danger-300 @enderror">
                    @error('form.category')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description (optional)</label>
                    <textarea id="description" wire:model="form.description" rows="3"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50 @error('form.description') border-danger-300 @enderror"></textarea>
                    @error('form.description')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="is_active" wire:model="form.is_active"
                        class="rounded border-gray-300 text-info-600 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50">
                    <label for="is_active" class="ml-2 text-sm text-gray-700">Active</label>
                </div>

                <!-- Variables Section -->
                <div>
                    <div class="flex justify-between items-center">
                        <label for="variables" class="block text-sm font-medium text-gray-700 mb-1">Template Variables (one per line)</label>
                        <button type="button" wire:click="detectVariables"
                            class="text-xs text-info-600 hover:text-info-800">
                            Detect Variables
                        </button>
                    </div>
                    <textarea id="variables" wire:model="form.variables" rows="5"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50 @error('form.variables') border-danger-300 @enderror"></textarea>
                    <p class="mt-1 text-xs text-gray-500">Example: name<br>email<br>verification_url</p>
                    @error('form.variables')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Detected Variables -->
                @if (count($detectedVariables) > 0)
                <div class="bg-info-50 p-3 rounded-md border border-info-200">
                    <h3 class="text-sm font-medium text-info-700 mb-1">Detected Variables</h3>
                    <ul class="list-disc pl-5 text-xs text-info-600 space-y-1">
                        @foreach ($detectedVariables as $variable)
                        <li>{{ $variable }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>

            <div class="space-y-6">
                <!-- Content Editor -->
                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-1">HTML Content</label>
                    <div x-data="{
                        editor: null,
                        init() {
                            // Initialize TinyMCE
                            tinymce.init({
                                selector: '#content',
                                plugins: 'code link lists image table',
                                toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image | table | code',
                                height: 400,
                                setup: (editor) => {
                                    this.editor = editor;
                                    editor.on('change', function (e) {
                                        @this.set('form.content', editor.getContent());
                                    });
                                    editor.on('init', function (e) {
                                        editor.setContent(@this.get('form.content'));
                                    });
                                }
                            });
                        },
                        updateContent() {
                            this.editor.setContent(@this.get('form.content'));
                        }
                    }"
                        x-init="init()"
                        @content-updated.window="updateContent()"
                        class="border border-gray-300 rounded-md"
                        style="min-height: 400px;">
                        <textarea id="content" wire:model.defer="form.content" class="hidden"></textarea>
                    </div>
                    @error('form.content')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Preview Section -->
                <div class="border border-gray-200 rounded-md p-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-sm font-medium text-gray-700">Preview</h3>
                        <button type="button" wire:click="generatePreview"
                            class="px-3 py-1.5 bg-info-600 text-white text-sm rounded hover:bg-info-700 transition">
                            Generate Preview
                        </button>
                    </div>

                    @if($previewActive)
                    <div class="border-t pt-4 mt-2">
                        <div class="mb-3 pb-3 border-b">
                            <h4 class="text-sm font-medium text-gray-700 mb-1">Subject:</h4>
                            <div class="text-sm p-2 bg-gray-50 rounded">{{ $previewSubject }}</div>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-1">Content:</h4>
                            <div class="border border-gray-200 rounded bg-white p-4 max-h-96 overflow-y-auto">
                                <div class="email-preview">
                                    {!! $previewHtml !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-8 text-gray-500 text-sm">
                        Click "Generate Preview" to see how your email will look
                    </div>
                    @endif

                    <!-- Preview Data Editor -->
                    <div class="mt-4 pt-4 border-t">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Preview Data</h4>
                        <div class="space-y-2 max-h-60 overflow-y-auto">
                            @foreach($previewData as $key => $value)
                            <div class="grid grid-cols-3 gap-2 items-center text-sm">
                                <div class="font-medium">{{ $key }}:</div>
                                <div class="col-span-2">
                                    <input type="text" wire:model="previewData.{{ $key }}"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-info-300 focus:ring focus:ring-info-200 focus:ring-opacity-50 text-sm py-1">
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-between border-t pt-6">
            <a href="{{ route('laravel-emails.templates.index') }}"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-info-600 text-white rounded hover:bg-info-700 transition">
                {{ $template->exists ? 'Update Template' : 'Create Template' }}
            </button>
        </div>
    </form>

    <!-- TinyMCE Script - Add this to your layout or include it here -->
    @push('scripts')
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    @endpush
</div>