@extends('laravel-emails::backend.layout')

@section('title', 'Email Layouts')

@section('content')
<div class="bg-white rounded-lg shadow-md">
    <div class="flex justify-between items-center p-4 border-b">
        <h1 class="text-xl font-semibold text-gray-800">Email Layouts</h1>
        <a href="{{ route('laravel-emails.layouts.create') }}"
            class="px-4 py-2 bg-info-600 text-white rounded hover:bg-info-700 transition">
            Create New Layout
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
                        Status
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Templates
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($layouts as $layout)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $layout->name }}
                        @if($layout->is_default)
                        <span
                            class="px-2 ml-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-info-100 text-info-800">
                            Default
                        </span>
                        @endif
                        @if($layout->is_system)
                        <span
                            class="px-2 ml-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                            System
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <code class="text-sm">{{ $layout->slug }}</code>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
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
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $layout->templates()->count() }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-3">
                            <a href="{{ route('laravel-emails.layouts.preview', $layout) }}"
                                class="text-primary-600 hover:text-primary-900" target="_blank">Preview</a>
                            <a href="{{ route('laravel-emails.layouts.edit', $layout) }}"
                                class="text-info-600 hover:text-info-900">Edit</a>
                            @unless($layout->is_system)
                            <form action="{{ route('laravel-emails.layouts.destroy', $layout) }}" method="POST"
                                onsubmit="return confirm('Are you sure you want to delete this layout?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-danger-600 hover:text-danger-900">Delete</button>
                            </form>
                            @endunless
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                        No email layouts found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t">
        {{ $layouts->links() }}
    </div>
</div>
@endsection