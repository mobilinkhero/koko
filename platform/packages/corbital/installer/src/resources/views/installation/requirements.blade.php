@extends('installer::installation.layout')

@section('content')
<div>
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">System Requirements</h2>

    <!-- PHP Version Check -->
    <div class="mb-8">
        <h3 class="text-lg font-medium text-gray-700 mb-4">PHP Version</h3>
        <div class="bg-gray-50 rounded-md overflow-hidden">
            <!-- Table header -->
            <div class="grid grid-cols-2 border-b border-gray-200">
                <div class="px-4 py-3 text-sm font-medium text-gray-600 bg-gray-100">REQUIRED PHP VERSION</div>
                <div class="px-4 py-3 text-sm font-medium text-gray-600 bg-gray-100">CURRENT</div>
            </div>

            <!-- PHP version row -->
            <div class="grid grid-cols-2">
                <div class="px-4 py-3 text-sm font-medium text-gray-800">
                    >= {{ $php['minimum'] }}
                </div>
                <div class="px-4 py-3 text-sm">
                    @if ($php['supported'])
                    <span class="text-green-500 font-medium flex items-center">
                        <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"></path>
                        </svg>
                        {{ $php['current'] }}
                    </span>
                    @else
                    <span class="text-red-500 font-medium flex items-center">
                        <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd"></path>
                        </svg>
                        {{ $php['current'] }}
                    </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Required PHP Extensions -->
    <div class="mb-8">
        <h3 class="text-lg font-medium text-gray-700 mb-4">Required PHP Extensions</h3>
        <div class="bg-gray-50 rounded-md overflow-hidden">
            <div class="grid grid-cols-2 border-b border-gray-200">
                <div class="px-4 py-3 text-sm font-medium text-gray-600 bg-gray-100">EXTENSION</div>
                <div class="px-4 py-3 text-sm font-medium text-gray-600 bg-gray-100">ENABLED</div>
            </div>

            @foreach ($requirements['results']['php'] as $extension => $enabled)
            <div class="grid grid-cols-2 border-b border-gray-200">
                <div class="px-4 py-3 text-sm font-medium text-gray-800">{{ $extension }}</div>
                <div class="px-4 py-3 text-sm">
                    @if ($enabled)
                    <span class="text-green-500 font-medium flex items-center">
                        <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"></path>
                        </svg>
                        Yes
                    </span>
                    @else
                    <span class="text-red-500 font-medium flex items-center">
                        <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd"></path>
                        </svg>
                        No
                    </span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Required PHP Functions -->
    <div class="mb-8">
        <h3 class="text-lg font-medium text-gray-700 mb-4">Required PHP Functions</h3>
        <div class="bg-gray-50 rounded-md overflow-hidden">
            <div class="grid grid-cols-2 border-b border-gray-200">
                <div class="px-4 py-3 text-sm font-medium text-gray-600 bg-gray-100">FUNCTION</div>
                <div class="px-4 py-3 text-sm font-medium text-gray-600 bg-gray-100">ENABLED</div>
            </div>

            @foreach ($requirements['results']['functions'] as $function => $enabled)
            <div class="grid grid-cols-2 border-b border-gray-200">
                <div class="px-4 py-3 text-sm font-medium text-gray-800">{{ $function }}</div>
                <div class="px-4 py-3 text-sm">
                    @if ($enabled)
                    <span class="text-green-500 font-medium flex items-center">
                        <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"></path>
                        </svg>
                        Yes
                    </span>
                    @else
                    <span class="text-red-500 font-medium flex items-center">
                        <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd"></path>
                        </svg>
                        No
                    </span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Recommended Extensions & Functions -->
    @if (!empty($requirements['recommended']['php']) || !empty($requirements['recommended']['functions']))
    <div class="mb-8">
        <h3 class="text-lg font-medium text-gray-700 mb-4">Recommended PHP Extensions/Functions</h3>
        <div class="bg-gray-50 rounded-md overflow-hidden">
            <div class="grid grid-cols-2 border-b border-gray-200">
                <div class="px-4 py-3 text-sm font-medium text-gray-600 bg-gray-100">REQUIREMENT</div>
                <div class="px-4 py-3 text-sm font-medium text-gray-600 bg-gray-100">ENABLED</div>
            </div>

            <!-- PHP Extensions -->
            @foreach ($requirements['recommended']['php'] as $extension => $enabled)
            <div class="grid grid-cols-2 border-b border-gray-200">
                <div class="px-4 py-3 text-sm font-medium text-gray-800">{{ $extension }}</div>
                <div class="px-4 py-3 text-sm">
                    @if ($enabled)
                    <span class="text-green-500 font-medium flex items-center">
                        <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"></path>
                        </svg>
                        Yes
                    </span>
                    @else
                    <span class="text-yellow-500 font-medium flex items-center">
                        <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd"></path>
                        </svg>
                        No
                    </span>
                    @endif
                </div>
            </div>
            @endforeach

            <!-- PHP Functions -->
            @foreach ($requirements['recommended']['functions'] as $function => $enabled)
            <div class="grid grid-cols-2 border-b border-gray-200">
                <div class="px-4 py-3 text-sm font-medium text-gray-800">{{ $function }}</div>
                <div class="px-4 py-3 text-sm">
                    @if ($enabled)
                    <span class="text-green-500 font-medium flex items-center">
                        <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"></path>
                        </svg>
                        Yes
                    </span>
                    @else
                    <span class="text-yellow-500 font-medium flex items-center">
                        <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd"></path>
                        </svg>
                        No
                    </span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="flex justify-end mt-8">
        @if ($requirements['errors'] || !$php['supported'])
        <p class="text-red-600 mr-4 flex items-center">
            <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd"
                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                    clip-rule="evenodd"></path>
            </svg>
            Please fix the requirements before continuing.
        </p>
        @else
        <a href="{{ route('install.permissions') }}"
            class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Next</a>
        @endif
    </div>
</div>
@endsection