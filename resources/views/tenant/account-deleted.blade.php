<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Deleted - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white shadow-lg rounded-lg p-6">
        <div class="text-center">
            <!-- Error Icon -->
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>

            <!-- Title -->
            <h1 class="text-2xl font-bold text-gray-900 mb-2">
                Account Deleted
            </h1>

            <!-- Message -->
            <p class="text-gray-600 mb-6">
                This account has been deleted on {{ $deletion_date->format('F j, Y') }} and is no longer accessible.
            </p>

            <!-- Details -->
            <div class="bg-gray-50 rounded-md p-4 mb-6">
                <div class="text-sm text-gray-700">
                    <p><strong>Company:</strong> {{ $tenant->company_name }}</p>
                    <p><strong>Domain:</strong> {{ $tenant->domain }}</p>
                    <p><strong>Deletion Date:</strong> {{ $deletion_date->format('M j, Y \a\t g:i A') }}</p>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="text-sm text-gray-500">
                <p>If you believe this is an error, please contact our support team.</p>
                <p class="mt-2">
                    <a href="mailto:{{ config('mail.from.address') }}"
                       class="text-blue-600 hover:text-blue-500 underline">
                        {{ config('mail.from.address') }}
                    </a>
                </p>
            </div>

            <!-- Back to Main Site -->
            <div class="mt-6">
                <a href="{{ url('/') }}"
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Return to {{ config('app.name') }}
                </a>
            </div>
        </div>
    </div>
</body>
</html>
