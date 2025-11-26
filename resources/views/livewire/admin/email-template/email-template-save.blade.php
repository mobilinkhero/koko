<!-- Email Template Editor View (email-template-save.blade.php) -->
<div x-data="emailTemplateForm()" x-cloak class="w-full m-auto ">
    <x-slot:title>
        {{ t('email_template_editor') }}
    </x-slot:title>

      <x-breadcrumb :items="[
        ['label' => t('dashboard'), 'route' => route('admin.dashboard')],
        ['label' => t('email_templates'), 'route' => route('admin.email-template.list')],
        ['label' => t('editing_template')]
]" />

    <form wire:submit="save">
        <!-- Main Layout: Two Cards Side by Side -->
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

            <!-- LEFT CARD: Template Form -->
            <div class="xl:col-span-6">
                <x-card class="h-fit">
                    <x-slot:header>
                        <div class="flex items-center space-x-2">
                            <x-heroicon-o-document-text class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                            <h2 class="text-lg font-semibold text-slate-700 dark:text-slate-300">
                                {{ t('template_configuration') }}
                            </h2>
                        </div>
                    </x-slot:header>

                    <x-slot:content class="space-y-8">

                        <!-- Template Basic Info -->
                        <x-card class="mb-6">
                            <!-- Template Basic Info -->
                            <div class="space-y-4">
                                <x-slot:header>
                                    <div class="flex items-center space-x-2">
                                        <x-heroicon-o-cog-6-tooth
                                            class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                                        <h2 class="text-lg font-semibold text-slate-700 dark:text-slate-300">
                                            {{ t('basic_information') }}
                                        </h2>
                                    </div>
                                </x-slot:header>
                                <x-slot:content>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <x-label for="name" :value="t('template_name')" class="mb-2" />
                                            <x-input type="text" id="name" wire:model="name" disabled
                                                class="cursor-not-allowed bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-600" />
                                            <x-input-error for="name" class="mt-1" />
                                        </div>

                                        <div>
                                            <x-label for="subject" :value="t('email_subject')" class="mb-2" />
                                            <x-input type="text" id="subject" wire:model="subject" x-ref="subjectInput"
                                                @click="updateCursorInfo('subject', $event)"
                                                @keyup="updateCursorInfo('subject', $event)"
                                                @focus="setActiveField('subject')"
                                                placeholder="{{ t('enter_email_subject') }}"
                                                class="transition-all duration-200 focus:ring-2 focus:ring-primary-500 focus:border-primary-500" />
                                            <x-input-error for="subject" class="mt-1" />
                                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                                {{ t('click_here_add_merge_fields') }}
                                            </p>
                                        </div>
                                    </div>
                                </x-slot:content>
                            </div>
                        </x-card>

                        <!-- Email Content Editor -->
                        <x-card class="mb-6">
                            <x-slot:header>
                                <div class="flex items-center space-x-2">
                                    <x-heroicon-o-pencil-square
                                        class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                                    <h2 class="text-lg font-semibold text-slate-700 dark:text-slate-300">
                                        {{ t('email_content') }}
                                    </h2>
                                </div>
                            </x-slot:header>

                            <x-slot:content>
                                <div class="space-y-4">
                                    <div>
                                        <x-label for="content" :value="t('message_content')" class="mb-2" />
                                        <div wire:ignore>
                                            <div x-data x-ref="editor" x-init="const quill = new Quill($refs.editor, {
                                                theme: 'snow',
                                                modules: {
                                                    toolbar: [
                                                        [{ 'header': [1, 2, 3, false] }],
                                                        ['bold', 'italic', 'underline'],
                                                        [{ 'color': [] }, { 'background': [] }],
                                                        [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                                                        [{ 'align': [] }],
                                                        ['link', 'image'],
                                                        ['clean']
                                                    ]
                                                }
                                            });

                                            window.quillEditor = quill;

                                            let timeout = null;

                                            quill.on('text-change', () => {
                                                clearTimeout(timeout);
                                                timeout = setTimeout(() => {
                                                    $wire.set('content', quill.root.innerHTML);
                                                }, 500);
                                            });

                                            quill.on('selection-change', (range) => {
                                                if (range) {
                                                    window.emailFormInstance.cursorPosition = range.index;
                                                    window.emailFormInstance.setActiveField('content');
                                                }
                                            });"
                                                class="rounded-lg border-2 border-slate-200 dark:border-slate-600 min-h-[350px] transition-all duration-200 focus-within:border-primary-500 focus-within:ring-2 focus-within:ring-primary-200 dark:focus-within:ring-primary-800"
                                                onclick="window.emailFormInstance.setActiveField('content')">
                                                {!! $content !!}
                                            </div>
                                        </div>
                                        <x-input-error for="content" class="mt-1" />
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                            {{ t('click_in_editor_to_add_merge_fields') }}
                                        </p>
                                    </div>
                                </div>
                            </x-slot:content>
                        </x-card>
                        <div x-show="showPreview" x-transition>
                            <!-- Email Preview Section -->
                            <x-card class="mb-6">
                                <!-- Header -->
                                <x-slot:header>
                                    <div class="flex items-center space-x-2">
                                        <x-heroicon-o-eye class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                                        <h2 class="text-lg font-semibold text-slate-700 dark:text-slate-300">
                                            {{ t('email_preview') }}
                                        </h2>
                                    </div>
                                </x-slot:header>

                                <!-- Content -->
                                <x-slot:content>
                                    @if ($layout)
                                    <div class="mb-4">
                                        <div x-data="{
                                                header: @js($layout->header),
                                                footer: @js($layout->footer),
                                                content: @entangle('content').defer,
                                                get currentContent() {
                                                    return window.quillEditor ? window.quillEditor.root.innerHTML : (@js($content) || '<p>No content yet...</p>');
                                                },
                                                rawTemplate: @js($layout->master_template),
                                                get renderedTemplate() {
                                                    return this.rawTemplate
                                                        .replace('{HEADER}', this.header || '')
                                                        .replace('{FOOTER}', this.footer || '')
                                                        .replace('{CONTENT}', this.currentContent);
                                                }
                                            }">
                                            <div x-html="renderedTemplate"></div>
                                        </div>
                                    </div>
                                    @endif
                                </x-slot:content>
                            </x-card>
                        </div>
                        <!-- Email Preview Section -->
                    </x-slot:content>
                    <x-slot:footer>
                        <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-4">
                            <x-button.secondary x-on:click="showPreview = !showPreview" type="button"
                                class="w-full sm:w-auto">
                                <div class="flex items-center justify-center">
                                    <x-heroicon-o-eye class="w-4 h-4 mr-2" />
                                    <span
                                        x-text="showPreview ? '{{ t('hide_preview') }}' : '{{ t('show_preview') }}'"></span>
                                </div>
                            </x-button.secondary>

                            <x-button.loading-button type="submit" target="save" class="w-full sm:w-auto">
                                <div class="flex items-center justify-center">
                                    <x-heroicon-o-check class="w-4 h-4 mr-2" />
                                    {{ t('save_template') }}
                                </div>
                            </x-button.loading-button>
                        </div>
                    </x-slot:footer>
                </x-card>
            </div>

            <!-- RIGHT CARD: Merge Fields -->
            <div class="xl:col-span-6">
                <x-card class="mb-6 self-start">
                    <!-- Header -->
                    <x-slot:header>
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <x-heroicon-o-squares-2x2 class="w-5 h-5 text-primary-500" />
                                    <h2 class="text-lg font-semibold text-slate-700 dark:text-slate-300">
                                        {{ t('merge_fields') }}
                                    </h2>
                                </div>
                                <div id="activeFieldIndicator" style="display: none;"
                                    class="text-xs px-3 py-1 bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 rounded-full font-medium">
                                    <span id="activeFieldText"></span>
                                </div>
                            </div>

                            <!-- Search Bar -->
                            <div class="relative">
                                <x-heroicon-o-magnifying-glass
                                    class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-slate-400" />
                                <input type="text" id="searchInput"
                                    oninput="window.emailFormInstance.handleSearch(this.value)"
                                    placeholder="{{ t('search_merge_fields') }}"
                                    class="w-full pl-10 pr-10 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all" />
                                <button type="button" id="clearSearchBtn"
                                    onclick="window.emailFormInstance.clearSearch()" style="display: none;"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                                    <x-heroicon-o-x-mark class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </x-slot:header>

                    <!-- Content -->
                    <x-slot:content>
                        <div>
                            @if (count($groupedFields) > 0)
                            <!-- Clean Tab Navigation -->
                            <div class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-600">
                                <div class="flex items-center">
                                    <!-- Left Arrow -->
                                    <button type="button" id="tabScrollLeft"
                                        onclick="window.emailFormInstance.scrollTabs('left')"
                                        class="flex-shrink-0 p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors mr-2"
                                        style="display: none;">
                                        <x-heroicon-o-chevron-left class="w-4 h-4" />
                                    </button>

                                    <!-- Scrollable Tab Container -->
                                    <div class="flex-1 overflow-hidden">
                                        <nav class="flex transition-transform duration-300 ease-in-out"
                                            id="tabNavigation">
                                            @foreach ($groupedFields as $groupKey => $fields)
                                            @php
                                            $groupLabel = Str::title(str_replace('-', ' ', $groupKey));
                                            @endphp
                                            <button type="button" data-tab="{{ $groupKey }}"
                                                onclick="window.emailFormInstance.switchTab('{{ $groupKey }}')"
                                                class="tab-button flex-shrink-0 px-4 py-3 text-sm font-medium border-b-2 transition-all duration-200"
                                                :class="activeTab === '{{ $groupKey }}'
                                                            ?
                                                            'border-primary-500 text-primary-600 dark:text-primary-400' :
                                                            'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'">
                                                {{ $groupLabel }}
                                                <span
                                                    class="ml-1.5 text-xs bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 px-1.5 py-0.5 rounded-full">{{
                                                    count($fields) }}</span>
                                            </button>
                                            @endforeach
                                        </nav>
                                    </div>

                                    <!-- Right Arrow -->
                                    <button type="button" id="tabScrollRight"
                                        onclick="window.emailFormInstance.scrollTabs('right')"
                                        class="flex-shrink-0 p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors ml-2"
                                        style="display: none;">
                                        <x-heroicon-o-chevron-right class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>

                            <!-- Fields Grid -->
                            <div class=" overflow-y-auto py-4">
                                @foreach ($groupedFields as $groupKey => $fields)
                                <div x-show="activeTab === '{{ $groupKey }}'" data-tab-content="{{ $groupKey }}"
                                    class="tab-content transition-all duration-200" x-cloak>
                                    <!-- 2-Column Grid Layout -->
                                    <div class="grid xl:grid-cols-3 grid-cols-1 md:grid-cols-2 gap-3">
                                        @foreach ($fields as $field)
                                        <div data-field-name="{{ $field['name'] }}" data-field-key="{{ $field['key'] }}"
                                            class="field-item">
                                            <button type="button"
                                                onclick="window.emailFormInstance.insertMergeField('{{ $field['key'] }}')"
                                                class="w-full text-left p-4 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl"
                                                title="{{ t('click_to_insert') }} {{ $field['name'] }}">
                                                <div class="flex items-center space-x-3">

                                                    <!-- Content -->
                                                    <div class="min-w-0 flex-1">
                                                        <div
                                                            class="font-semibold text-sm text-slate-800 dark:text-slate-200 mb-1">
                                                            {{ $field['name'] }}
                                                        </div>
                                                        <div
                                                            class="text-xs text-slate-500 dark:text-slate-400 font-mono bg-slate-100 dark:bg-slate-600 px-2 py-1 rounded-md truncate">
                                                            {{ $field['key'] }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </button>
                                        </div>
                                        @endforeach
                                    </div>

                                    <!-- No Results in This Tab -->
                                    <div class="text-center py-12 no-results-group" style="display: none;">
                                        <div
                                            class="w-16 h-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                            <x-heroicon-o-magnifying-glass class="w-8 h-8 text-slate-400" />
                                        </div>
                                        <p class="text-slate-500 dark:text-slate-400 font-medium">
                                            {{ t('no_matching_fields_in_group') }}</p>
                                    </div>

                                    <!-- Empty State for Tab -->
                                    @if (count($fields) === 0)
                                    <div class="text-center py-12">
                                        <div
                                            class="w-16 h-16 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                            <x-heroicon-o-document-text class="w-8 h-8 text-slate-400" />
                                        </div>
                                        <p class="text-slate-500 dark:text-slate-400 font-medium">
                                            {{ t('no_fields_in_category') }}</p>
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>

                            <!-- Overall No Results -->
                            <div class="text-center py-16 overall-no-results" style="display: none;">
                                <div
                                    class="w-20 h-20 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-6">
                                    <x-heroicon-o-magnifying-glass class="w-10 h-10 text-slate-400" />
                                </div>
                                <p class="text-lg font-medium text-slate-600 dark:text-slate-400 mb-2">
                                    {{ t('no_fields_found') }}</p>
                                <p class="text-slate-500 dark:text-slate-400 mb-4">{{ t('try_to_adjust_search_terms') }}
                                </p>
                                <button type="button" onclick="window.emailFormInstance.clearSearch()"
                                    class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                                    {{ t('clear_search_and_show_all') }}
                                </button>
                            </div>
                            @else
                            <div class="p-16 text-center">
                                <div
                                    class="w-24 h-24 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-6">
                                    <x-heroicon-o-document-text class="w-12 h-12 text-slate-400" />
                                </div>
                                <p class="text-xl font-medium text-slate-600 dark:text-slate-400 mb-2">
                                    {{ t('no_merge_fields_available') }}</p>
                                <p class="text-slate-500 dark:text-slate-400">
                                    {{ t('contact_administrator_to_add_fields') }}</p>
                            </div>
                            @endif
                        </div>
                    </x-slot:content>
                </x-card>

            </div>
        </div>
    </form>

    <script>
        function emailTemplateForm() {
            return {
                showPreview: false,
                processedPreviewContent: '',
                activeField: null,
                cursorPosition: 0,
                searchQuery: '',
                activeTab: @json(array_key_first($groupedFields)),
                scrollPosition: 0,

                init() {
                    // Store instance globally for vanilla JS access
                    window.emailFormInstance = this;

                    // Setup arrow visibility after a short delay to ensure DOM is ready
                    setTimeout(() => {
                        this.updateArrowVisibility();
                    }, 100);
                },

                scrollTabs(direction) {
                    const tabNav = document.getElementById('tabNavigation');
                    if (!tabNav) return;

                    const scrollAmount = 200; // pixels to scroll
                    const currentScroll = this.scrollPosition;

                    if (direction === 'left') {
                        this.scrollPosition = Math.max(0, currentScroll - scrollAmount);
                    } else {
                        const maxScroll = tabNav.scrollWidth - tabNav.parentElement.clientWidth;
                        this.scrollPosition = Math.min(maxScroll, currentScroll + scrollAmount);
                    }

                    tabNav.style.transform = `translateX(-${this.scrollPosition}px)`;
                    this.updateArrowVisibility();
                },

                updateArrowVisibility() {
                    const tabNav = document.getElementById('tabNavigation');
                    const leftArrow = document.getElementById('tabScrollLeft');
                    const rightArrow = document.getElementById('tabScrollRight');

                    if (!tabNav || !leftArrow || !rightArrow) return;

                    const containerWidth = tabNav.parentElement.clientWidth;
                    const contentWidth = tabNav.scrollWidth;
                    const maxScroll = contentWidth - containerWidth;

                    // Show/hide left arrow
                    if (this.scrollPosition > 0) {
                        leftArrow.style.display = 'block';
                    } else {
                        leftArrow.style.display = 'none';
                    }

                    // Show/hide right arrow
                    if (this.scrollPosition < maxScroll && maxScroll > 0) {
                        rightArrow.style.display = 'block';
                    } else {
                        rightArrow.style.display = 'none';
                    }
                },

                switchTab(tabKey) {
                    this.activeTab = tabKey;

                    // Update tab buttons with border design
                    document.querySelectorAll('.tab-button').forEach(btn => {
                        btn.classList.remove('border-primary-500', 'text-primary-600', 'dark:text-primary-400');
                        btn.classList.add('border-transparent', 'text-slate-500', 'dark:text-slate-400');
                    });

                    const activeBtn = document.querySelector(`[data-tab="${tabKey}"]`);
                    if (activeBtn) {
                        activeBtn.classList.add('border-primary-500', 'text-primary-600', 'dark:text-primary-400');
                        activeBtn.classList.remove('border-transparent', 'text-slate-500', 'dark:text-slate-400');
                    }

                    // Update tab content
                    document.querySelectorAll('.tab-content').forEach(content => {
                        content.classList.add('hidden');
                        content.classList.remove('block');
                    });

                    const activeContent = document.querySelector(`[data-tab-content="${tabKey}"]`);
                    if (activeContent) {
                        activeContent.classList.remove('hidden');
                        activeContent.classList.add('block');
                    }

                    // Reapply search filter to new tab
                    if (this.searchQuery) {
                        this.filterFields();
                    }
                },

                setActiveField(field) {
                    this.activeField = field;
                    this.updateActiveFieldIndicator();
                },

                updateActiveFieldIndicator() {
                    const indicator = document.getElementById('activeFieldIndicator');
                    const text = document.getElementById('activeFieldText');

                    if (this.activeField && indicator && text) {
                        indicator.style.display = 'block';
                        text.textContent = this.activeField === 'subject' ? '{{ t('subject') }}' : '{{ t('content') }}';
                    } else if (indicator) {
                        indicator.style.display = 'none';
                    }
                },

                updateCursorInfo(field, event) {
                    if (field === 'subject') {
                        this.cursorPosition = event.target.selectionStart;
                        this.activeField = 'subject';
                        this.updateActiveFieldIndicator();
                    }
                },

                insertMergeField(fieldKey) {
                    if (this.activeField === 'subject') {
                        this.insertIntoSubject(fieldKey);
                    } else if (this.activeField === 'content') {
                        this.insertIntoQuill(fieldKey);
                    } else {
                        this.insertIntoQuill(fieldKey);
                        this.activeField = 'content';
                        this.updateActiveFieldIndicator();
                    }
                },

                insertIntoSubject(fieldKey) {
                    const subjectInput = this.$refs.subjectInput;
                    const currentValue = subjectInput.value;
                    const position = this.cursorPosition || currentValue.length;

                    const newValue = currentValue.slice(0, position) + fieldKey + currentValue.slice(position);
                    subjectInput.value = newValue;

                    this.$wire.set('subject', newValue);

                    setTimeout(() => {
                        subjectInput.focus();
                        const newPosition = position + fieldKey.length;
                        subjectInput.setSelectionRange(newPosition, newPosition);
                        this.cursorPosition = newPosition;
                    }, 10);
                },

                insertIntoQuill(fieldKey) {
                    if (window.quillEditor) {
                        const quill = window.quillEditor;
                        const range = quill.getSelection();
                        const position = range ? range.index : quill.getLength();

                        quill.insertText(position, fieldKey);
                        quill.setSelection(position + fieldKey.length);

                        setTimeout(() => {
                            this.$wire.set('content', quill.root.innerHTML);
                        }, 100);
                    }
                },

                handleSearch(query) {
                    this.searchQuery = query;
                    this.filterFields();

                    // Update UI elements
                    const clearBtn = document.getElementById('clearSearchBtn');
                    if (clearBtn) {
                        clearBtn.style.display = query.length > 0 ? 'block' : 'none';
                    }
                },

                filterFields() {
                    let totalVisibleCount = 0;

                    // Filter across all tab contents
                    document.querySelectorAll('.tab-content').forEach(tabContent => {
                        const fields = tabContent.querySelectorAll('.field-item');
                        const noResultsDiv = tabContent.querySelector('.no-results-group');
                        let visibleCount = 0;

                        fields.forEach(field => {
                            const fieldName = field.dataset.fieldName.toLowerCase();
                            const fieldKey = field.dataset.fieldKey.toLowerCase();
                            const query = this.searchQuery.toLowerCase();

                            if (!this.searchQuery || fieldName.includes(query) || fieldKey.includes(
                                    query)) {
                                field.style.display = 'block';
                                visibleCount++;
                                totalVisibleCount++;
                            } else {
                                field.style.display = 'none';
                            }
                        });

                        // Show/hide "no results" message for this tab
                        if (noResultsDiv) {
                            if (visibleCount === 0 && this.searchQuery.length > 0) {
                                noResultsDiv.style.display = 'block';
                            } else {
                                noResultsDiv.style.display = 'none';
                            }
                        }
                    });

                    // Show/hide overall "no results" message
                    const overallNoResults = document.querySelector('.overall-no-results');
                    if (overallNoResults) {
                        if (totalVisibleCount === 0 && this.searchQuery.length > 0) {
                            overallNoResults.style.display = 'block';
                            // Hide all tab contents when showing overall no results
                            document.querySelectorAll('.tab-content').forEach(content => {
                                content.classList.add('hidden');
                                content.classList.remove('block');
                            });
                        } else {
                            overallNoResults.style.display = 'none';
                            // Ensure proper tab visibility - hide all first, then show active
                            document.querySelectorAll('.tab-content').forEach(content => {
                                content.classList.add('hidden');
                                content.classList.remove('block');
                            });

                            // Show only the active tab
                            const activeContent = document.querySelector(`[data-tab-content="${this.activeTab}"]`);
                            if (activeContent) {
                                activeContent.classList.remove('hidden');
                                activeContent.classList.add('block');
                            }
                        }
                    }
                },

                clearSearch() {
                    this.searchQuery = '';
                    const searchInput = document.getElementById('searchInput');
                    if (searchInput) {
                        searchInput.value = '';
                    }

                    // Clear the search filter first
                    this.filterFields();

                    const clearBtn = document.getElementById('clearSearchBtn');
                    if (clearBtn) {
                        clearBtn.style.display = 'none';
                    }

                    // Hide overall no results message
                    const overallNoResults = document.querySelector('.overall-no-results');
                    if (overallNoResults) {
                        overallNoResults.style.display = 'none';
                    }

                    // Hide all no-results-group messages in tabs
                    document.querySelectorAll('.no-results-group').forEach(noResults => {
                        noResults.style.display = 'none';
                    });

                    // Properly reset tab visibility - hide all tabs first
                    document.querySelectorAll('.tab-content').forEach(content => {
                        content.classList.add('hidden');
                        content.classList.remove('block');
                        content.style.display = ''; // Reset any inline styles
                    });

                    // Show only the active tab
                    const activeContent = document.querySelector(`[data-tab-content="${this.activeTab}"]`);
                    if (activeContent) {
                        activeContent.classList.remove('hidden');
                        activeContent.classList.add('block');
                    }
                },

                togglePreview() {
                    this.showPreview = !this.showPreview;
                    const previewDiv = document.getElementById('emailPreview');
                    const buttonText = document.getElementById('previewButtonText');

                    if (previewDiv && buttonText) {
                        if (this.showPreview) {
                            previewDiv.style.display = 'block';
                            buttonText.textContent = '{{ t('hide_preview') }}';
                            this.updatePreview();
                        } else {
                            previewDiv.style.display = 'none';
                            buttonText.textContent = '{{ t('show_preview') }}';
                        }
                    }
                },

                hidePreview() {
                    this.showPreview = false;
                    const previewDiv = document.getElementById('emailPreview');
                    const buttonText = document.getElementById('previewButtonText');

                    if (previewDiv && buttonText) {
                        previewDiv.style.display = 'none';
                        buttonText.textContent = '{{ t('show_preview') }}';
                    }
                },

                updatePreview() {
                    const content = this.$wire.content || '';
                    const processedContent = this.replaceMergeFields(content);
                    const previewElement = document.getElementById('previewContent');

                    if (previewElement) {
                        previewElement.innerHTML = processedContent;
                    }
                },

                replaceMergeFields(content) {
                    return content.replace(/\{[^}]+\}/g,
                        '<span class="bg-warning-100 dark:bg-warning-800 text-warning-800 dark:text-warning-200 px-2 py-1 rounded text-sm font-mono">[Sample Value]</span>'
                    );
                }
            }
        }
    </script>
</div>