<!-- resources/views/livewire/tenant/ai/personal-assistant-manager.blade.php -->
<div>
    <!-- Page Header -->
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">AI Assistant</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Manage your personal AI assistant to help with FAQs, product enquiries, onboarding, and more.
            </p>
        </div>
        
        <div class="mt-4 sm:mt-0">
            @if(!$assistant)
            <button wire:click="createAssistant" type="button" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <x-heroicon-m-plus class="-ml-1 mr-2 h-5 w-5" />
                Create New Assistant
            </button>
            @else
            <button wire:click="createAssistant" type="button" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <x-heroicon-m-plus class="-ml-1 mr-2 h-5 w-5" />
                Create New Assistant
            </button>
            @endif
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
    <div class="mt-4 bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-md">
        {{ session('success') }}
    </div>
    @endif

    @if (session()->has('error'))
    <div class="mt-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-md">
        {{ session('error') }}
    </div>
    @endif

    @if (session()->has('file-upload-success'))
    <div class="mt-4 bg-blue-50 border border-blue-200 text-blue-600 px-4 py-3 rounded-md">
        {{ session('file-upload-success') }}
    </div>
    @endif

    <!-- No Assistant State -->
    @if(!$assistant && !$showCreateForm)
    <div class="mt-8 text-center py-12">
        <x-heroicon-o-cpu-chip class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No Personal Assistant Yet</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Create your first AI assistant to help with document analysis, customer support, and more.
        </p>
        <div class="mt-6">
            <button wire:click="createAssistant" type="button" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700">
                <x-heroicon-m-plus class="-ml-1 mr-2 h-5 w-5" />
                Create Assistant
            </button>
        </div>
    </div>
    @endif

    <!-- AI Assistant Card -->
    @if(!$showCreateForm && $assistants && $assistants->count() > 0)
    @foreach($assistants as $assistant)
    <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 max-w-md">
        <!-- Header with Icon and Title -->
        <div class="flex items-start space-x-3 mb-4">
            <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <x-heroicon-s-sparkles class="w-6 h-6 text-purple-600" />
                </div>
            </div>
            <div class="flex-1">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $assistant->name }}</h3>
                        <div class="flex items-center space-x-2 mt-1">
                            <span class="text-xs font-medium {{ $assistant->is_active ? 'text-green-600' : 'text-gray-500' }}">
                                {{ $assistant->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <span class="text-xs text-gray-500">{{ $availableModels[$assistant->model] ?? 'gpt-4o-mini' }}</span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-xs text-gray-500">{{ $assistant->is_active ? 'Active' : 'Inactive' }}</span>
                        <button 
                            wire:click="toggleAssistant({{ $assistant->id }})"
                            type="button"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 {{ $assistant->is_active ? 'bg-purple-600' : 'bg-gray-300' }}"
                        >
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform duration-200 ease-in-out {{ $assistant->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Description -->
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            {{ $assistant->description ?: 'An intelligent virtual assistant designed to streamline your workflows, provide real-time insights,...' }}
        </p>

        <!-- Document Count -->
        <div class="flex items-center space-x-2 mb-6">
            <x-heroicon-o-document class="w-4 h-4 text-gray-400" />
            <span class="text-sm text-gray-600">{{ $assistant->hasUploadedFiles() ? $assistant->getFileCount() : 1 }} document</span>
        </div>

        <!-- Expandable Sections -->
        <div class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-4">
            <!-- OpenAI Integration -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <x-heroicon-s-cube class="w-5 h-5 text-blue-600" />
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">OpenAI Integration</h4>
                            <p class="text-xs text-gray-500">AI Assistant Status & Sync Information</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Items -->
            <div class="space-y-3">
                <!-- Sync Status -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        @php
                            $allSynced = true;
                            if ($assistant->hasUploadedFiles()) {
                                foreach ($assistant->uploaded_files as $file) {
                                    if (!isset($file['synced']) || !$file['synced']) {
                                        $allSynced = false;
                                        break;
                                    }
                                }
                            } else {
                                $allSynced = false;
                            }
                        @endphp
                        <div class="w-2 h-2 {{ $allSynced ? 'bg-green-400' : 'bg-yellow-400' }} rounded-full"></div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Sync Status</span>
                    </div>
                    <span class="text-xs font-medium {{ $allSynced ? 'text-green-600 bg-green-50' : 'text-yellow-600 bg-yellow-50' }} px-2 py-1 rounded">
                        {{ $allSynced ? '100% Synced' : '0% Synced' }}
                    </span>
                </div>

                <!-- AI Assistant -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="w-2 h-2 {{ $assistant->is_active ? 'bg-green-400' : 'bg-gray-400' }} rounded-full"></div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">AI Assistant</span>
                    </div>
                    <span class="text-xs font-medium {{ $assistant->is_active ? 'text-green-600 bg-green-50' : 'text-gray-600 bg-gray-100' }} px-2 py-1 rounded">
                        {{ $assistant->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <!-- Knowledge Base -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="w-2 h-2 {{ $assistant->hasUploadedFiles() ? 'bg-green-400' : 'bg-gray-400' }} rounded-full"></div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Knowledge Base</span>
                    </div>
                    <span class="text-xs font-medium {{ $assistant->hasUploadedFiles() ? 'text-green-600 bg-green-50' : 'text-gray-600 bg-gray-100' }} px-2 py-1 rounded">
                        {{ $assistant->hasUploadedFiles() ? 'Ready' : 'Pending' }}
                    </span>
                </div>
            </div>

            <!-- Documents Status -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Documents Status</span>
                    <span class="text-xs text-gray-500">{{ $assistant->hasUploadedFiles() ? $assistant->getFileCount() : 0 }} total</span>
                </div>
                @php
                    $syncedCount = 0;
                    $pendingCount = 0;
                    if ($assistant->hasUploadedFiles()) {
                        foreach ($assistant->uploaded_files as $file) {
                            if (isset($file['synced']) && $file['synced']) {
                                $syncedCount++;
                            } else {
                                $pendingCount++;
                            }
                        }
                    }
                @endphp
                
                @if($syncedCount > 0)
                <div class="bg-green-50 dark:bg-green-900/20 rounded px-3 py-2 mb-1">
                    <span class="text-xs font-medium text-green-700 dark:text-green-400">{{ $syncedCount }} Synced</span>
                </div>
                @endif
                
                @if($pendingCount > 0)
                <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded px-3 py-2">
                    <span class="text-xs font-medium text-yellow-700 dark:text-yellow-400">{{ $pendingCount }} Pending</span>
                </div>
                @elseif($syncedCount == 0 && !$assistant->hasUploadedFiles())
                <div class="bg-gray-50 dark:bg-gray-900/20 rounded px-3 py-2">
                    <span class="text-xs font-medium text-gray-700 dark:text-gray-400">No documents</span>
                </div>
                @endif
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center space-x-3 mt-6">
            <button wire:click="openChat({{ $assistant->id }})" class="flex-1 bg-blue-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors flex items-center justify-center shadow-sm h-10">
                <x-heroicon-s-chat-bubble-left-right class="w-4 h-4 mr-2" />
                Chat
            </button>
            <button wire:click="syncAssistant({{ $assistant->id }})" wire:loading.attr="disabled" class="flex-1 bg-purple-600 text-white px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-purple-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-sm h-10 min-w-0">
                <span wire:loading.remove wire:target="syncAssistant({{ $assistant->id }})" class="flex items-center justify-center whitespace-nowrap">
                    Sync Now
                </span>
                <span wire:loading wire:target="syncAssistant({{ $assistant->id }})" class="flex items-center justify-center whitespace-nowrap">
                    <svg class="animate-spin h-4 w-4 mr-2 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="truncate">Syncing...</span>
                </span>
            </button>
            <button wire:click="openDetails({{ $assistant->id }})" class="flex-1 bg-gray-700 dark:bg-gray-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 dark:hover:bg-gray-700 transition-colors flex items-center justify-center shadow-sm h-10" title="View Details">
                <x-heroicon-s-information-circle class="w-4 h-4 mr-2" />
                Details
            </button>
            <button wire:click="editSpecificAssistant({{ $assistant->id }})" class="w-10 h-10 p-0 bg-gray-100 dark:bg-gray-700 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors flex items-center justify-center shadow-sm" title="Edit Assistant">
                <x-heroicon-s-cog-6-tooth class="w-5 h-5 text-gray-600 dark:text-gray-400" />
            </button>
            <button wire:click="deleteSpecificAssistant({{ $assistant->id }})" wire:confirm="Delete assistant?" class="w-10 h-10 p-0 bg-red-100 dark:bg-red-900/20 rounded-full hover:bg-red-200 dark:hover:bg-red-900/40 transition-colors flex items-center justify-center shadow-sm" title="Delete Assistant">
                <x-heroicon-s-trash class="w-5 h-5 text-red-600 dark:text-red-400" />
            </button>
        </div>
    </div>
    @endforeach
    @elseif(!$showCreateForm)
    <div class="text-center py-12">
        <x-heroicon-o-cpu-chip class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No AI Assistants</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Get started by creating your first AI assistant.
        </p>
    </div>
    @endif

    <!-- Create/Edit Form -->
    @if($showCreateForm)
    <div class="mt-6 bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                {{ $assistant ? 'Edit Assistant' : 'New Personal Assistant' }}
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                Configure your AI assistant for document analysis, customer support, and automation.
            </p>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-5 sm:px-6">
            <form wire:submit.prevent="saveAssistant" class="space-y-6">
                <!-- Basic Info -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assistant Name *</label>
                        <input wire:model="name" type="text" id="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm" placeholder="e.g., SmartFlow AI">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="model" class="block text-sm font-medium text-gray-700 dark:text-gray-300">AI Model *</label>
                        <select wire:model="model" id="model" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                            @foreach($availableModels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('model') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                    <textarea wire:model="description" id="description" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm" placeholder="Brief description of what this assistant helps with"></textarea>
                    @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Use Cases -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Use Cases</label>
                    <div class="mt-2 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach($useCaseOptions as $value => $label)
                        <label class="relative flex items-start">
                            <div class="flex items-center h-5">
                                <input wire:model="use_case_tags" type="checkbox" value="{{ $value }}" class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600">
                            </div>
                            <div class="ml-3 text-sm">
                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $label }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @error('use_case_tags') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Advanced Settings -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="temperature" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Temperature (Creativity): {{ $temperature }}
                        </label>
                        <input wire:model.live="temperature" type="range" id="temperature" min="0" max="2" step="0.1" class="mt-1 block w-full">
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>Focused (0)</span>
                            <span>Balanced (1)</span>
                            <span>Creative (2)</span>
                        </div>
                        @error('temperature') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="max_tokens" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Response Length</label>
                        <select wire:model="max_tokens" id="max_tokens" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                            <option value="500">Short (500 tokens)</option>
                            <option value="1000">Medium (1000 tokens)</option>
                            <option value="2000">Long (2000 tokens)</option>
                            <option value="4000">Very Long (4000 tokens)</option>
                        </select>
                        @error('max_tokens') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- System Instructions -->
                <div>
                    <label for="system_instructions" class="block text-sm font-medium text-gray-700 dark:text-gray-300">System Instructions *</label>
                    <textarea wire:model="system_instructions" id="system_instructions" rows="6" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm" placeholder="Define how the assistant should behave, its role, and guidelines..."></textarea>
                    <p class="mt-1 text-xs text-gray-500">These instructions guide how your AI assistant responds to queries.</p>
                    @error('system_instructions') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- File Upload -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Upload Files for AI Analysis</label>
                    
                    <!-- Show existing files if editing -->
                    @if($editingAssistantId && $assistant && $assistant->hasUploadedFiles())
                    <div class="mt-2 mb-4 bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Currently Uploaded Files</h4>
                        <div class="space-y-2">
                            @foreach($assistant->getFilesWithStatus() as $file)
                            <div class="flex items-center justify-between bg-white dark:bg-gray-800 rounded p-2">
                                <div class="flex items-center space-x-2">
                                    @if($file['type'] === 'csv')
                                    <x-heroicon-s-table-cells class="h-4 w-4 text-green-500" />
                                    @elseif(in_array($file['type'], ['txt', 'md']))
                                    <x-heroicon-s-document-text class="h-4 w-4 text-blue-500" />
                                    @elseif($file['type'] === 'json')
                                    <x-heroicon-s-code-bracket class="h-4 w-4 text-purple-500" />
                                    @else
                                    <x-heroicon-s-document class="h-4 w-4 text-gray-500" />
                                    @endif
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $file['original_name'] }}</span>
                                    <span class="text-xs text-gray-500">({{ number_format($file['size'] ?? 0) }} bytes)</span>
                                </div>
                                <button type="button" wire:click="removeFile('{{ $file['original_name'] }}')" class="text-red-600 hover:text-red-800">
                                    <x-heroicon-s-x-mark class="h-4 w-4" />
                                </button>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md dark:border-gray-600">
                        <div class="space-y-1 text-center">
                            <x-heroicon-o-cloud-arrow-up class="mx-auto h-12 w-12 text-gray-400" />
                            <div class="flex text-sm text-gray-600">
                                <label for="file-upload" class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-primary-600 hover:text-primary-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500">
                                    <span>Upload {{ $editingAssistantId ? 'additional' : '' }} files</span>
                                    <input wire:model="files" id="file-upload" name="file-upload" type="file" class="sr-only" multiple accept=".txt,.md,.csv,.json">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">
                                TXT, MD, CSV, JSON up to 5MB each
                            </p>
                        </div>
                    </div>
                    
                    @if(count($files) > 0)
                    <div class="mt-2 space-y-1">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">New files to upload:</h4>
                        @foreach($files as $file)
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            📁 {{ $file->getClientOriginalName() }} ({{ number_format($file->getSize()) }} bytes)
                        </div>
                        @endforeach
                    </div>
                    @endif
                    
                    @error('files') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    @error('files.*') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <!-- Guidelines -->
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md p-4">
                    <div class="flex">
                        <x-heroicon-o-information-circle class="h-5 w-5 text-blue-400" />
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">Guidelines for Best Results</h3>
                            <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Define the assistant's role and expertise area</li>
                                    <li>Specify the tone and communication style</li>
                                    <li>Include any specific guidelines or limitations</li>
                                    <li>Upload relevant documents for context and knowledge</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3">
                    <button wire:click="cancelForm" type="button" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        {{ $assistant ? 'Update Assistant' : 'Create Assistant' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Chat Modal -->
    @if($showChatModal && $chattingAssistantId)
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity z-50" wire:click="closeChat"></div>
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-xl bg-white dark:bg-gray-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-3xl" wire:click.stop>
                @php
                    $chattingAssistant = $assistants->find($chattingAssistantId);
                @endphp
                
                <!-- Simplified Header -->
                <div class="bg-white dark:bg-gray-800 px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $chattingAssistant->name ?? 'SmartFlow AI' }}</h3>
                        <div class="flex items-center space-x-2">
                            <button wire:click="closeChat" class="text-gray-400 hover:text-gray-600 px-3 py-1 text-sm">
                                <x-heroicon-s-arrow-left class="w-4 h-4 inline mr-1" />
                                Back to assistant
                            </button>
                            <button wire:click="clearChat" class="px-3 py-1.5 text-sm bg-red-500 text-white rounded-md hover:bg-red-600 flex items-center">
                                <x-heroicon-s-trash class="w-4 h-4 mr-1" />
                                Clear Chat
                            </button>
                            <button class="px-3 py-1.5 text-sm bg-green-500 text-white rounded-md hover:bg-green-600 flex items-center">
                                <x-heroicon-s-check-circle class="w-4 h-4 mr-1" />
                                Synced
                            </button>
                            <button wire:click="closeChat" class="text-gray-400 hover:text-gray-600 ml-2">
                                <x-heroicon-s-x-mark class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="flex bg-white dark:bg-gray-800">
                    <!-- Chat Messages and Input Area -->
                    <div class="flex-1 flex flex-col">
                        <!-- Purple Header Bar -->
                        <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                                    <x-heroicon-s-sparkles class="w-5 h-5 text-white" />
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-white font-medium">{{ $chattingAssistant->name ?? 'SmartFlow AI' }}</h4>
                                    <p class="text-xs text-white/80">{{ $availableModels[$chattingAssistant->model] ?? 'gpt-3.5-turbo' }}</p>
                                </div>
                                <span class="text-xs text-white/80 bg-white/20 px-2 py-1 rounded">
                                    {{ $chattingAssistant->hasUploadedFiles() ? $chattingAssistant->getFileCount() : 1 }} Documents
                                </span>
                            </div>
                        </div>

                        <!-- Chat Messages Area -->
                        <div class="flex-1 overflow-y-auto p-6 bg-gray-50 dark:bg-gray-900" id="chat-messages">
                            @foreach($chatMessages as $message)
                            <div class="mb-4">
                                @if($message['role'] === 'assistant')
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                            <x-heroicon-s-sparkles class="w-5 h-5 text-purple-600" />
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $message['content'] }}</p>
                                        <p class="text-xs text-gray-400 mt-1">{{ $message['timestamp'] }}</p>
                                    </div>
                                </div>
                                @else
                                <div class="flex justify-end">
                                    <div class="max-w-xs lg:max-w-md">
                                        <div class="bg-purple-600 text-white rounded-lg px-4 py-2">
                                            <p class="text-sm">{{ $message['content'] }}</p>
                                        </div>
                                        <p class="text-xs text-gray-400 text-right mt-1">{{ $message['timestamp'] }}</p>
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>

                        <!-- Message Input -->
                        <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-4 bg-white dark:bg-gray-800">
                            <form wire:submit.prevent="sendMessage" class="flex items-center space-x-3">
                                <input 
                                    type="text" 
                                    wire:model="currentMessage"
                                    placeholder="Type your message..."
                                    class="flex-1 rounded-lg border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-purple-500 focus:ring-purple-500 text-sm px-4 py-2"
                                    autofocus
                                >
                                <button 
                                    type="submit"
                                    class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors flex items-center text-sm font-medium"
                                >
                                    <x-heroicon-s-paper-airplane class="w-4 h-4" />
                                    <span class="ml-2">Send</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Knowledge Base Sidebar -->
                    <div class="w-72 border-l border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                        <div class="p-4">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                <x-heroicon-s-folder class="w-4 h-4 mr-2" />
                                Knowledge Base
                            </h4>
                            
                            @if($chattingAssistant && $chattingAssistant->hasUploadedFiles())
                            <div class="space-y-2">
                                @foreach($chattingAssistant->getFilesWithStatus() as $file)
                                <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-700">
                                    <div class="flex items-start space-x-2">
                                        <x-heroicon-s-document class="w-4 h-4 text-green-500 mt-0.5" />
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $file['original_name'] }}</p>
                                            <p class="text-xs text-green-600 mt-1">Synced</p>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-700">
                                <div class="flex items-start space-x-2">
                                    <x-heroicon-s-document class="w-4 h-4 text-green-500 mt-0.5" />
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">SmartFlow.docx</p>
                                        <p class="text-xs text-green-600 mt-1">Synced</p>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Script to auto-scroll chat -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('scroll-to-bottom', () => {
                setTimeout(() => {
                    const chatMessages = document.getElementById('chat-messages');
                    if (chatMessages) {
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    }
                }, 100);
            });
        });
    </script>
    @endif

    <!-- Details Modal -->
    @if($showDetailsModal && $detailsAssistantId)
    @php
        $detailsAssistant = $assistants->find($detailsAssistantId);
        $details = null;
        if ($detailsAssistant) {
            $totalFiles = $detailsAssistant->hasUploadedFiles() ? count($detailsAssistant->uploaded_files) : 0;
            $syncedFiles = 0;
            if ($detailsAssistant->hasUploadedFiles()) {
                foreach ($detailsAssistant->uploaded_files as $file) {
                    if (isset($file['synced']) && $file['synced']) {
                        $syncedFiles++;
                    }
                }
            }
            $syncProgress = $totalFiles > 0 ? round(($syncedFiles / $totalFiles) * 100) : ($detailsAssistant->openai_assistant_id ? 100 : 0);
            $overallStatus = $detailsAssistant->openai_assistant_id ? 'Fully Synced' : 'Not Synced';
            
            $details = [
                'id' => $detailsAssistant->id,
                'name' => $detailsAssistant->name,
                'model' => $detailsAssistant->model,
                'is_active' => $detailsAssistant->is_active,
                'openai_assistant_id' => $detailsAssistant->openai_assistant_id,
                'openai_vector_store_id' => $detailsAssistant->openai_vector_store_id,
                'total_documents' => $totalFiles,
                'synced_documents' => $syncedFiles,
                'sync_progress' => $syncProgress,
                'overall_status' => $overallStatus,
                'last_synced_at' => $detailsAssistant->last_synced_at,
                'has_knowledge_base' => $detailsAssistant->hasUploadedFiles(),
            ];
        }
    @endphp
    @if($details && $detailsAssistant)
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity z-50" wire:click="closeDetails"></div>
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-xl bg-white dark:bg-gray-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl" wire:click.stop>
                <!-- Header -->
                <div class="bg-white dark:bg-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <x-heroicon-s-cube class="w-6 h-6 text-blue-600" />
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">OpenAI Integration Details</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $detailsAssistant->name ?? 'AI Assistant' }}</p>
                            </div>
                        </div>
                        <button wire:click="closeDetails" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <x-heroicon-s-x-mark class="w-6 h-6" />
                        </button>
                    </div>
                </div>

                <!-- Content -->
                <div class="bg-white dark:bg-gray-800 px-6 py-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Assistant Overview -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center space-x-2 mb-4">
                                <x-heroicon-s-chart-bar class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Assistant Overview</h4>
                            </div>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Total Documents</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $details['total_documents'] }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Sync Progress</span>
                                    <span class="text-sm font-medium text-green-600">{{ $details['sync_progress'] }}%</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Overall Status</span>
                                    <div class="flex items-center space-x-2">
                                        <div class="w-2 h-2 {{ $details['overall_status'] === 'Fully Synced' ? 'bg-green-500' : 'bg-yellow-500' }} rounded-full"></div>
                                        <span class="text-sm font-medium {{ $details['overall_status'] === 'Fully Synced' ? 'text-green-600' : 'text-yellow-600' }}">{{ $details['overall_status'] }}</span>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ $details['sync_progress'] }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- OpenAI Resources -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center space-x-2 mb-4">
                                <x-heroicon-s-cloud class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">OpenAI Resources</h4>
                            </div>
                            <div class="space-y-4">
                                <!-- AI Assistant -->
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">AI Assistant</span>
                                        <div class="flex items-center space-x-2">
                                            <div class="w-2 h-2 {{ $details['openai_assistant_id'] ? 'bg-green-500' : 'bg-gray-400' }} rounded-full"></div>
                                            <span class="text-xs font-medium {{ $details['openai_assistant_id'] ? 'text-green-600' : 'text-gray-600' }}">
                                                {{ $details['openai_assistant_id'] ? 'Created' : 'Not Created' }}
                                            </span>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">OpenAI Assistant Instance</p>
                                    @if($details['openai_assistant_id'])
                                    <div class="bg-white dark:bg-gray-800 rounded p-2 border border-gray-200 dark:border-gray-600">
                                        <p class="text-xs font-mono text-gray-700 dark:text-gray-300 break-all">{{ $details['openai_assistant_id'] }}</p>
                                    </div>
                                    @else
                                    <p class="text-xs text-gray-500 italic">Click "Sync Now" to create</p>
                                    @endif
                                </div>

                                <!-- Knowledge Base -->
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Knowledge Base</span>
                                        <div class="flex items-center space-x-2">
                                            <div class="w-2 h-2 {{ $details['openai_vector_store_id'] ? 'bg-green-500' : 'bg-gray-400' }} rounded-full"></div>
                                            <span class="text-xs font-medium {{ $details['openai_vector_store_id'] ? 'text-green-600' : 'text-gray-600' }}">
                                                {{ $details['openai_vector_store_id'] ? 'Ready' : 'Pending' }}
                                            </span>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Vector Store for Document Search</p>
                                    @if($details['openai_vector_store_id'])
                                    <div class="bg-white dark:bg-gray-800 rounded p-2 border border-gray-200 dark:border-gray-600">
                                        <p class="text-xs font-mono text-gray-700 dark:text-gray-300 break-all">{{ $details['openai_vector_store_id'] }}</p>
                                    </div>
                                    @else
                                    <p class="text-xs text-gray-500 italic">Will be created on sync</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Document Status Summary -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center space-x-2 mb-4">
                                <x-heroicon-s-document class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Document Status Summary</h4>
                            </div>
                            <div class="space-y-3">
                                @if($details['total_documents'] > 0)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Fully Synced</span>
                                    <span class="text-sm font-medium text-green-600 bg-green-50 dark:bg-green-900/20 px-2 py-1 rounded">
                                        {{ $details['synced_documents'] }} document{{ $details['synced_documents'] !== 1 ? 's' : '' }}
                                    </span>
                                </div>
                                @if($details['synced_documents'] < $details['total_documents'])
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Pending</span>
                                    <span class="text-sm font-medium text-yellow-600 bg-yellow-50 dark:bg-yellow-900/20 px-2 py-1 rounded">
                                        {{ $details['total_documents'] - $details['synced_documents'] }} document{{ ($details['total_documents'] - $details['synced_documents']) !== 1 ? 's' : '' }}
                                    </span>
                                </div>
                                @endif
                                @else
                                <p class="text-sm text-gray-500 italic">No documents uploaded</p>
                                @endif
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center space-x-2 mb-4">
                                <x-heroicon-s-bolt class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Quick Actions</h4>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <button wire:click="editSpecificAssistant({{ $detailsAssistantId }})" class="flex items-center justify-center space-x-2 bg-purple-600 text-white px-3 py-2 rounded-lg text-xs font-medium hover:bg-purple-700 transition-colors">
                                    <x-heroicon-s-cog-6-tooth class="w-4 h-4" />
                                    <span>Manage</span>
                                </button>
                                <button wire:click="openChat({{ $detailsAssistantId }})" class="flex items-center justify-center space-x-2 bg-blue-600 text-white px-3 py-2 rounded-lg text-xs font-medium hover:bg-blue-700 transition-colors">
                                    <x-heroicon-s-chat-bubble-left-right class="w-4 h-4" />
                                    <span>Open Chat</span>
                                </button>
                                @if($details['openai_assistant_id'])
                                <a href="https://platform.openai.com/assistants" target="_blank" class="flex items-center justify-center space-x-2 bg-sky-500 text-white px-3 py-2 rounded-lg text-xs font-medium hover:bg-sky-600 transition-colors">
                                    <x-heroicon-s-arrow-top-right-on-square class="w-4 h-4" />
                                    <span>OpenAI Dashboard</span>
                                </a>
                                @else
                                <button disabled class="flex items-center justify-center space-x-2 bg-gray-400 text-white px-3 py-2 rounded-lg text-xs font-medium cursor-not-allowed">
                                    <x-heroicon-s-arrow-top-right-on-square class="w-4 h-4" />
                                    <span>Not Available</span>
                                </button>
                                @endif
                                <button wire:click="syncAssistant({{ $detailsAssistantId }})" class="flex items-center justify-center space-x-2 bg-purple-600 text-white px-3 py-2 rounded-lg text-xs font-medium hover:bg-purple-700 transition-colors">
                                    <x-heroicon-s-arrow-path class="w-4 h-4" />
                                    <span>Sync Now</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    @if($details['last_synced_at'])
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            @php
                                $lastSynced = $details['last_synced_at'];
                                if (is_string($lastSynced)) {
                                    $lastSynced = \Carbon\Carbon::parse($lastSynced);
                                }
                            @endphp
                            Last synced: {{ $lastSynced->format('M d, Y h:i A') }}
                        </p>
                    </div>
                    @endif
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 border-t border-gray-200 dark:border-gray-600 flex justify-between items-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400">OpenAI Integration Status Dashboard</p>
                    <button wire:click="closeDetails" class="px-4 py-2 bg-gray-600 text-white rounded-lg text-sm font-medium hover:bg-gray-700 transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endif
</div>
