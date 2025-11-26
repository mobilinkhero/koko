@extends('laravel-emails::backend.layout')

@section('title', 'Email Templates')

@section('content')
<div class="bg-white rounded-lg shadow-md">
    <div class="flex justify-between items-center p-4 border-b">
        <h1 class="text-xl font-semibold text-gray-800">Email Templates</h1>
        <a href="{{ route('laravel-emails.templates.create') }}"
            class="px-4 py-2 bg-info-600 text-white rounded hover:bg-info-700 transition">
            {{ t('create_template') }}
        </a>
    </div>

    @if(session('success'))
    <div class="bg-success-100 border-l-4 border-success-500 text-success-700 p-4 mb-4" role="alert">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-danger-100 border-l-4 border-danger-500 text-danger-700 p-4 mb-4" role="alert">
        {{ session('error') }}
    </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Name
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Slug
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Subject
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Category
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($templates as $template)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            {{ $template->name }}
                        </div>
                        @if($template->description)
                        <div class="text-sm text-gray-500 truncate max-w-xs">
                            {{ $template->description }}
                        </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $template->slug }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900 truncate max-w-xs">{{ $template->subject }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($template->category)
                        <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                            {{ $template->category }}
                        </span>
                        @else
                        <span class="text-gray-500 text-sm">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($template->is_active)
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

                        @if($template->is_system)
                        <span
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-info-100 text-info-800 ml-1">
                            System
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <a href="{{ route('laravel-emails.templates.show', $template) }}"
                                class="text-info-600 hover:text-info-900">View</a>

                            <a href="{{ route('laravel-emails.templates.edit', $template) }}"
                                class="text-primary-600 hover:text-primary-900">Edit</a>

                            @if(!$template->is_system)
                            <form action="{{ route('laravel-emails.templates.destroy', $template) }}" method="POST"
                                onsubmit="return confirm('Are you sure you want to delete this template?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-danger-600 hover:text-danger-900">Delete</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        No email templates found. <a href="{{ route('laravel-emails.templates.create') }}"
                            class="text-info-600 hover:underline">Create your first template</a>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection