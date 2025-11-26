@extends('installer::installation.layout')

@section('content')
<div>
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Files and folders permissions</h2>

    <p class="mb-2 text-gray-700">These folders must be writable by web server user: <span class="font-semibold">
            {{ \Corbital\Installer\Classes\PermissionsChecker::getCurrentProcessUser() }}</span>
    </p>
    <p class="mb-6 text-gray-700">Recommended permissions: <span class="font-semibold">0755</span></p>

    <div class="bg-gray-50 rounded-md overflow-hidden">
        <div class="grid grid-cols-3 border-b border-gray-200">
            <div class="px-4 py-3 text-sm font-medium text-gray-600 bg-gray-100">PATH</div>
            <div class="px-4 py-3 text-sm font-medium text-gray-600 bg-gray-100">PERMISSION</div>
            <div class="px-4 py-3 text-sm font-medium text-gray-600 bg-gray-100">STATUS</div>
        </div>

        @foreach ($permissions['items'] as $permission)
        <div class="grid grid-cols-3 border-b border-gray-200 last:border-b-0">
            <div class="px-4 py-3 text-sm font-medium text-gray-800">{{ $permission['folder'] }}</div>
            <div class="px-4 py-3 text-sm font-medium text-gray-800">{{ $permission['permission'] }}</div>
            <div class="px-4 py-3 text-sm">
                @if (!$permission['exists'])
                <span class="text-red-500 font-medium flex items-center">
                    <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd"></path>
                    </svg>
                    Folder Not Found
                </span>
                @elseif($permission['isWritable'])
                <span class="text-green-500 font-medium flex items-center">
                    <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd"></path>
                    </svg>
                    Writable
                </span>
                @else
                <span class="text-red-500 font-medium flex items-center">
                    <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd"></path>
                    </svg>
                    Not Writable
                </span>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    @if ($permissions['errors'])
    <div class="mt-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-yellow-700">
                    <span class="font-medium">Permission issues detected!</span>
                    <br>Please fix the folder permissions using the following commands:
                </p>
                <div class="mt-2 bg-gray-800 text-gray-200 p-3 rounded-md font-mono text-xs overflow-x-auto">
                    @php
                    $permissionsChecker = new \Corbital\Installer\Classes\PermissionsChecker();
                    $suggestions = $permissionsChecker->getFixSuggestions();
                    @endphp
                    @foreach ($suggestions as $suggestion)
                    <p>{{ $suggestion }}</p>
                    @endforeach
                </div>
                <p class="mt-2 text-sm text-yellow-700">Note: Replace "www-data" with the actual user that runs your
                    web
                    server.</p>
            </div>
        </div>
    </div>
    @endif

    <div class="flex justify-end mt-8">
        @if ($permissions['errors'])
        <p class="text-red-600 mr-4 flex items-center">
            <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd"
                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                    clip-rule="evenodd"></path>
            </svg>
            Please fix the folder permissions before continuing.
        </p>
        @else
        <a href="{{ route('install.setup') }}"
            class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">Next</a>
        @endif
    </div>
</div>
@endsection