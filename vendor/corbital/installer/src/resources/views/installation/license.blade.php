@extends('installer::installation.layout')

@section('content')
<div>
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">License Verification</h2>

    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 mb-6">
        <h3 class="text-lg font-medium text-gray-800 mb-4">Verify Your Purchase</h3>

        <p class="text-gray-600 mb-4">
            Please enter your Envato username and purchase code to validate your license.
            This step is required to continue with the installation.
        </p>

        <div class="flex items-center p-4 mb-4 text-sm text-info-800 rounded-lg bg-info-50 dark:bg-gray-800 dark:text-info-400"
            role="alert">
            <svg class="flex-shrink-0 inline w-4 h-4 mr-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                fill="currentColor" viewBox="0 0 20 20">
                <path
                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
            </svg>
            <span class="sr-only">Info</span>
            <div>
                <span class="font-medium">Need help?</span> You can find your purchase code in your Envato dashboard
                under Downloads > View Purchase Code.
                <a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-"
                    class="text-info-700 hover:underline" target="_blank">Learn more</a>
            </div>
        </div>
    </div>

    <form action="{{ route('install.license.verify') }}" method="POST">
        @csrf

        @if ($errors->has('general'))
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
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
                        {{ $errors->first('general') }}
                    </p>
                </div>
            </div>
        </div>
        @endif

        <div class="space-y-6">
            <div>
                <label for="username" class="block mb-1 text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Envato Username
                </label>
                <input type="text" id="username" name="username" value="{{ old('username', $username) }}"
                    class="w-full px-3 py-2 border @error('username') border-red-300 @else border-gray-300 @enderror rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Enter your Envato username" autocomplete="off">
                <p class="mt-1 text-xs text-gray-500">
                    The username you used to purchase the product on Envato Market.
                </p>
                @error('username')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="purchase_code" class="block mb-1 text-sm font-medium text-gray-700">
                    <span class="text-red-500">*</span> Purchase Code
                </label>
                <input type="text" id="purchase_code" name="purchase_code"
                    value="{{ old('purchase_code', $purchaseCode) }}"
                    class="w-full px-3 py-2 border @error('purchase_code') border-red-300 @else border-gray-300 @enderror rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" autocomplete="off">
                <p class="mt-1 text-xs text-gray-500">
                    Format: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
                </p>
                @error('purchase_code')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex justify-between mt-8">
            <a href="{{ route('install.permissions') }}"
                class="inline-flex items-center justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back
            </a>

            <button type="submit"
                class="inline-flex items-center justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Verify License
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        </div>
    </form>
</div>
@endsection