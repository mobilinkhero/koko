@extends('installer::installation.layout')

@section('content')
<div>
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Installation Successful</h2>

    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-8">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-green-700">
                    <span class="font-semibold">Congratulations!</span> The application has been successfully installed.
                </p>
            </div>
        </div>
    </div>

    <p class="text-gray-700 mb-4">As the last requirement, you must configure a cron job:</p>

    <div class="bg-gray-800 text-white p-4 rounded-md mb-6 text-sm font-mono overflow-x-auto">
        <div class="flex items-center mb-1">
            <code>{{ $phpExecutable }}</code>
            <button onclick="copyToClipboard('{{ $phpExecutable }}')" class="ml-2 p-1 text-gray-400 hover:text-white">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z"></path>
                    <path
                        d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z">
                    </path>
                </svg>
            </button>
        </div>
    </div>

    <p class="text-gray-700 mb-8">
        If you are not certain on how to configure the cron job with the minimum required PHP version
        ({{ config('installer.minPhpVersion') }}), the best is to consult with your hosting provider.
    </p>

    <p class="text-gray-700 mb-2">
        On some shared hostings you may need to specify full path to the PHP executable (for example, <code
            class="bg-gray-100 px-1 py-0.5 rounded">/usr/local/bin/php82</code> or <code
            class="bg-gray-100 px-1 py-0.5 rounded">/opt/alt/php82/usr/bin/php</code>) instead of <code
            class="bg-gray-100 px-1 py-0.5 rounded">php</code>.
    </p>

    <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mt-8">
        <div class="px-4 py-5 sm:px-6 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Admin Credentials</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Use these credentials to login to your application.</p>
        </div>
        <div class="border-t border-gray-200">
            <dl>
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Email Address:</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $user->email }}</dd>
                </div>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Password:</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">Your chosen password</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="flex justify-center mt-8">
        <a href="{{ url('/') }}"
            class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Go to Login
        </a>
    </div>
</div>

<script>
    function copyToClipboard(text) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);

            // Show a notification
            const notification = document.createElement('div');
            notification.className =
                'fixed bottom-4 right-4 bg-gray-800 text-white py-2 px-4 rounded-md shadow-lg transition-opacity';
            notification.textContent = 'Copied to clipboard!';
            document.body.appendChild(notification);

            // Remove notification after 2 seconds
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 2000);
        }
</script>
@endsection