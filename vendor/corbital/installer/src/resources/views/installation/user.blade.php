@extends('installer::installation.layout')

@section('content')
<div>
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Configure Admin User</h2>

    <form action="{{ route('install.user.store') }}" method="POST">
        @csrf

        <div class="space-y-6">
            @if (config('installer.admin_setup.fields.firstname', true))
            <div>
                <label for="firstname" class="block mb-1 text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> First Name
                </label>
                <input type="text" id="firstname" name="firstname" value="{{ old('firstname') }}"
                    class="w-full px-3 py-2 border @error('firstname') border-red-300 @else border-gray-300 @enderror rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Enter first name" autocomplete="off">
                @error('firstname')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            @endif

            @if (config('installer.admin_setup.fields.lastname', true))
            <div>
                <label for="lastname" class="block mb-1 text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Last Name
                </label>
                <input type="text" id="lastname" name="lastname" value="{{ old('lastname') }}"
                    class="w-full px-3 py-2 border @error('lastname') border-red-300 @else border-gray-300 @enderror rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Enter last name" autocomplete="off">
                @error('lastname')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            @endif

            <div>
                <label for="email" class="block mb-1 text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> E-Mail Address
                </label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                    class="w-full px-3 py-2 border @error('email') border-red-300 @else border-gray-300 @enderror rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Enter your email address that will be used for login" autocomplete="off">
                @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="timezone" class="block mb-1 text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Timezone
                </label>
                <select id="timezone" name="timezone"
                    class="w-full px-3 py-2 border @error('timezone') border-red-300 @else border-gray-300 @enderror rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    required>
                    <option value="">Select Timezone</option>
                    @foreach (timezone_identifiers_list() as $timezone)
                    <option value="{{ $timezone }}" @if (old('timezone')==$timezone || (!old('timezone') &&
                        $timezone=='UTC' )) selected @endif>
                        {{ $timezone }}</option>
                    @endforeach
                </select>
                @error('timezone')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block mb-1 text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Password
                </label>
                <input type="password" id="password" name="password"
                    class="w-full px-3 py-2 border @error('password') border-red-300 @else border-gray-300 @enderror rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Choose a secure password" autocomplete="off">
                <p class="mt-1 text-xs text-gray-500">
                    Password must be at least 8 characters long.
                </p>
                @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block mb-1 text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Confirm Password
                </label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Confirm your password" autocomplete="off">
            </div>
        </div>

        <div class="flex justify-end mt-8">
            <button type="submit"
                class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Finish Installation
            </button>
        </div>
    </form>
</div>
@endsection