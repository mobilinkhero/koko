@extends('installer::installation.layout')

@section('content')
<div>
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">General Config</h2>

    <form action="{{ route('install.setup.store') }}" method="POST">
        @csrf

        <div class="space-y-6">
            @error('general')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <div>
                <label for="app_url" class="block mb-1 text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> App URL
                </label>
                <input type="text" id="app_url" name="app_url" value="{{ old('app_url', $guessedUrl) }}"
                    class="w-full px-3 py-2 border @error('app_url') border-red-300 @else border-gray-300 @enderror rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="https://example.com" autocomplete="off">
                <p class="mt-1 text-xs text-gray-500">
                    This is the URL where you are installing the application, for example, for subdomain in this field
                    you need to enter "https://subdomain.example.com/".
                </p>
                @error('app_url')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="app_name" class="block mb-1 text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Application Name
                </label>
                <input type="text" id="app_name" name="app_name"
                    value="{{ old('app_name', config('app.name', 'Laravel')) }}"
                    class="w-full px-3 py-2 border @error('app_name') border-red-300 @else border-gray-300 @enderror rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="My Application" autocomplete="off">
                @error('app_name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="country" class="block mb-1 text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Country
                </label>
                <select id="country" name="country"
                    class="w-full px-3 py-2 border @error('country') border-red-300 @else border-gray-300 @enderror rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Select Country</option>
                    @foreach ($countries as $country)
                    <option value="{{ $country->id }}" @if (old('country')==$country->id) selected @endif>
                        {{ $country->short_name }}</option>
                    @endforeach
                </select>
                @error('country')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <h2 class="text-2xl font-semibold text-gray-800 mt-10 mb-6">Database Configuration</h2>

        <div class="space-y-6">
            <div>
                <label for="database_hostname" class="block mb-1 text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Hostname
                </label>
                <input type="text" id="database_hostname" name="database_hostname"
                    value="{{ old('database_hostname', 'localhost') }}"
                    class="w-full px-3 py-2 border @error('database_hostname') border-red-300 @else border-gray-300 @enderror rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="localhost" autocomplete="off">
                @error('database_hostname')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="database_port" class="block mb-1 text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Port
                </label>
                <input type="text" id="database_port" name="database_port" value="{{ old('database_port', '3306') }}"
                    class="w-full px-3 py-2 border @error('database_port') border-red-300 @else border-gray-300 @enderror rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="3306" autocomplete="off">
                <p class="mt-1 text-xs text-gray-500">
                    The default MySQL port is 3306, change the value only if you are certain that you are using
                    different port.
                </p>
                @error('database_port')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="database_name" class="block mb-1 text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Database Name
                </label>
                <input type="text" id="database_name" name="database_name" value="{{ old('database_name') }}"
                    class="w-full px-3 py-2 border @error('database_name') border-red-300 @else border-gray-300 @enderror rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    autocomplete="off">
                <p class="mt-1 text-xs text-gray-500">
                    Make sure that you have created the database before configuring.
                </p>
                @error('database_name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="database_username" class="block mb-1 text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Database Username
                </label>
                <input type="text" id="database_username" name="database_username"
                    value="{{ old('database_username') }}"
                    class="w-full px-3 py-2 border @error('database_username') border-red-300 @else border-gray-300 @enderror rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    autocomplete="off">
                <p class="mt-1 text-xs text-gray-500">
                    Make sure you have set ALL privileges for the user.
                </p>
                @error('database_username')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="database_password" class="block mb-1 text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Database Password
                </label>
                <input type="password" id="database_password" name="database_password"
                    value="{{ old('database_password') }}"
                    class="w-full px-3 py-2 border @error('database_password') border-red-300 @else border-gray-300 @enderror rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    autocomplete="off">
                <p class="mt-1 text-xs text-gray-500">
                    Enter the database user password.
                </p>
                @error('database_password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        @if (session('database_error'))
        <div class="mt-6 bg-red-50 border-l-4 border-red-500 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">
                        <span class="font-medium">Database connection error:</span>
                        <br>{{ session('database_error') }}
                    </p>
                </div>
            </div>
        </div>
        @endif

        <div class="flex justify-end mt-8">
            <button type="submit"
                class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Test Connection & Configure
            </button>
        </div>
    </form>
</div>
@endsection