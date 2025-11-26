@extends('laravel-emails::backend.layout')

@section('title', 'Layout Details')

@section('content')
<div class="bg-white rounded-lg shadow-md">
    <div class="flex justify-between items-center p-4 border-b">
        <h1 class="text-xl font-semibold text-gray-800">{{ $layout->name }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('laravel-emails.layouts.edit', $layout) }}"
                class="px-4 py-2 bg-info-600 text-white rounded hover:bg-info-700 transition">
                Edit Layout
            </a>
            <a href="{{ route('laravel-emails.layouts.preview', $layout) }}" target="_blank"
                class="px-4 py-2 bg-success-600 text-white rounded hover:bg-success-700 transition">
                Preview
            </a>
            <a href="{{ route('laravel-emails.layouts.index') }}"
                class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition">
                Back to Layouts
            </a>
        </div>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <div class="mb-6">
                    <h2 class="text-lg font-medium mb-2">Layout Details</h2>
                    <div class="bg-gray-50 rounded p-4 border border-gray-200">
                        <dl class="grid grid-cols-3 gap-1">
                            <dt class="font-medium text-gray-600">Name:</dt>
                            <dd class="col-span-2">{{ $layout->name }}</dd>

                            <dt class="font-medium text-gray-600">Slug:</dt>
                            <dd class="col-span-2"><code
                                    class="bg-gray-100 px-1 py-0.5 rounded">{{ $layout->slug }}</code></dd>

                            <dt class="font-medium text-gray-600">Status:</dt>
                            <dd class="col-span-2">
                                @if($layout->is_active)
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-success-100 text-success-800">
                                    Active
                                </span>
                                @else
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-danger-100 text-danger-800">
                                    Inactive
                                </span>
                                @endif
                            </dd>

                            <dt class="font-medium text-gray-600">Default:</dt>
                            <dd class="col-span-2">
                                @if($layout->is_default)
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-info-100 text-info-800">
                                    Default Layout
                                </span>
                                @else
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Not Default
                                </span>
                                @endif
                            </dd>

                            <dt class="font-medium text-gray-600">System:</dt>
                            <dd class="col-span-2">
                                @if($layout->is_system)
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                    System Layout
                                </span>
                                @else
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Custom Layout
                                </span>
                                @endif
                            </dd>

                            <dt class="font-medium text-gray-600">Templates:</dt>
                            <dd class="col-span-2">{{ $layout->templates()->count() }}</dd>

                            <dt class="font-medium text-gray-600">Created:</dt>
                            <dd class="col-span-2">{{ $layout->created_at ? $layout->created_at->format('M d, Y H:i:s')
                                : 'N/A' }}</dd>

                            <dt class="font-medium text-gray-600">Updated:</dt>
                            <dd class="col-span-2">{{ $layout->updated_at ? $layout->updated_at->format('M d, Y H:i:s')
                                : 'N/A' }}</dd>

                            <dt class="font-medium text-gray-600">Variables:</dt>
                            <dd class="col-span-2">
                                @if(!empty($layout->variables) && is_array($layout->variables))
                                <div class="flex flex-wrap gap-1">
                                    @foreach($layout->variables as $variable)
                                    <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs">{{ $variable
                                        }}</span>
                                    @endforeach
                                </div>
                                @else
                                <em class="text-gray-500">No variables defined</em>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div>
                <div class="mb-6">
                    <h2 class="text-lg font-medium mb-2">Associated Templates</h2>
                    <div class="bg-gray-50 rounded p-4 border border-gray-200">
                        @if($layout->templates()->count() > 0)
                        <ul class="divide-y divide-gray-200">
                            @foreach($layout->templates as $template)
                            <li class="py-2">
                                <div class="flex items-center justify-between">
                                    <a href="{{ route('laravel-emails.templates.edit', $template) }}"
                                        class="text-info-600 hover:text-info-800 font-medium">
                                        {{ $template->name }}
                                    </a>
                                    <span class="text-sm text-gray-500">{{ $template->slug }}</span>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                        @else
                        <div class="text-center py-4 text-gray-500">
                            No templates are using this layout yet.
                        </div>
                        @endif
                    </div>
                </div>

                <div class="mb-6">
                    <h2 class="text-lg font-medium mb-2">Layout Preview</h2>
                    <div class="bg-gray-50 rounded p-4 border border-gray-200 text-center">
                        <a href="{{ route('laravel-emails.layouts.preview', $layout) }}" target="_blank"
                            class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded hover:bg-primary-700 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            View Preview
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection