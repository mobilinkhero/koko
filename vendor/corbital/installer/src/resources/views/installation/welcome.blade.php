@extends('installer::installation.layout')

@section('content')
<div>
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Welcome to the Installer</h2>

    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 mb-6">
        <h3 class="text-lg font-medium text-gray-800 mb-4">Installation Process</h3>

        <p class="text-gray-600 mb-4">
            This wizard will guide you through the installation process. In just a few steps,
            you'll have your application up and running.
        </p>

        <div class="space-y-3 mb-4">
            <div class="flex items-start">
                <div
                    class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 font-semibold text-sm mr-3">
                    1</div>
                <div>
                    <h4 class="font-medium text-gray-800">Requirements Check</h4>
                    <p class="text-sm text-gray-600">We'll check if your server meets all the requirements.</p>
                </div>
            </div>

            <div class="flex items-start">
                <div
                    class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 font-semibold text-sm mr-3">
                    2</div>
                <div>
                    <h4 class="font-medium text-gray-800">Permissions</h4>
                    <p class="text-sm text-gray-600">We'll verify that file permissions are set correctly.</p>
                </div>
            </div>

            <div class="flex items-start">
                <div
                    class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 font-semibold text-sm mr-3">
                    3</div>
                <div>
                    <h4 class="font-medium text-gray-800">Environment Setup</h4>
                    <p class="text-sm text-gray-600">Set up your application environment and database connection.</p>
                </div>
            </div>

            <div class="flex items-start">
                <div
                    class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 font-semibold text-sm mr-3">
                    4</div>
                <div>
                    <h4 class="font-medium text-gray-800">Create Admin User</h4>
                    <p class="text-sm text-gray-600">Create your administrator account.</p>
                </div>
            </div>

            <div class="flex items-start">
                <div
                    class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 font-semibold text-sm mr-3">
                    5</div>
                <div>
                    <h4 class="font-medium text-gray-800">Finish</h4>
                    <p class="text-sm text-gray-600">Complete the installation and start using your application.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
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
                    <span class="font-medium">Before you begin:</span> Make sure you have your database credentials
                    ready.
                </p>
            </div>
        </div>
    </div>

    @if(strpos($url, '/public') !== false)
    <div class="bg-red-50 border-l-4 border-red-400 p-4 mt-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-red-700">
                    <span class="font-medium">IMPORTANT : </span>
                    Ensure that the document root path of your subdomain is set to the /public directory. This step is
                    crucial for the correct functioning of the application.
                    <a href="https://docs.corbitaltech.dev/" class="font-medium underline" target="_blank">Click
                        here</a> to view
                    the system requirements and setup documentation.
                </p>
            </div>
        </div>
    </div>
    @else
    <div class="flex justify-end mt-8">
        <a href="{{ route('install.requirements') }}"
            class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Start Installation
        </a>
    </div>
    @endif
</div>
@endsection