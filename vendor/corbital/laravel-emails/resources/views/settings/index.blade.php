<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Email Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(session('success'))
                    <div class="mb-4 p-4 text-sm text-success-700 bg-success-100 rounded-lg" role="alert">
                        {{ session('success') }}
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="mb-4 p-4 text-sm text-danger-700 bg-danger-100 rounded-lg" role="alert">
                        {{ session('error') }}
                    </div>
                    @endif

                    <div class="mb-4">
                        <div class="border-b border-gray-200">
                            <nav class="-mb-px flex" aria-label="Tabs">
                                <button type="button"
                                    class="tab-button active border-primary-500 text-primary-600 py-4 px-1 border-b-2 font-medium text-sm"
                                    data-tab="general">
                                    General Settings
                                </button>
                                <button type="button"
                                    class="tab-button ml-8 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 py-4 px-1 border-b-2 font-medium text-sm"
                                    data-tab="smtp">
                                    SMTP Configuration
                                </button>
                                <button type="button"
                                    class="tab-button ml-8 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 py-4 px-1 border-b-2 font-medium text-sm"
                                    data-tab="queue">
                                    Queue Settings
                                </button>
                            </nav>
                        </div>
                    </div>

                    <!-- Fixed Top Save Bar - always visible -->
                    <form id="settings-form" method="POST" action="{{ route('laravel-emails.settings.save') }}">
                        @csrf
                        <div
                            class="sticky top-0 bg-white shadow-md p-4 mb-6 rounded-lg flex justify-between items-center z-50">
                            <div class="text-sm font-medium text-gray-600">
                                <span id="current-tab-display">General Settings</span>
                            </div>
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-danger-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow-md hover:bg-danger-700 focus:bg-danger-700 active:bg-danger-800 focus:outline-none focus:ring-2 focus:ring-danger-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4">
                                    </path>
                                </svg>
                                Save Settings
                            </button>
                        </div>

                        <!-- General Settings Tab -->
                        <div id="general" class="tab-content active">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="col-span-1">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Sender Information</h3>

                                    <div class="mb-4">
                                        <label for="sender_name" class="block text-sm font-medium text-gray-700">Sender
                                            Name</label>
                                        <input type="text" name="sender_name" id="sender_name"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                            value="{{ old('sender_name', $settings->sender_name) }}" required>
                                        @error('sender_name')
                                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="sender_email" class="block text-sm font-medium text-gray-700">Sender
                                            Email</label>
                                        <input type="email" name="sender_email" id="sender_email"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                            value="{{ old('sender_email', $settings->sender_email) }}" required>
                                        @error('sender_email')
                                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="default_layout_template"
                                            class="block text-sm font-medium text-gray-700">Default Layout
                                            Template</label>
                                        <input type="text" name="default_layout_template" id="default_layout_template"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                            value="{{ old('default_layout_template', $settings->default_layout_template) }}">
                                        @error('default_layout_template')
                                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="email_signature"
                                            class="block text-sm font-medium text-gray-700">Email Signature</label>
                                        <textarea name="email_signature" id="email_signature" rows="4"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">{{ old('email_signature', $settings->email_signature) }}</textarea>
                                        @error('email_signature')
                                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                        @enderror
                                        <p class="mt-1 text-xs text-gray-500">HTML is supported in the signature</p>
                                    </div>
                                </div>

                                <div class="col-span-1">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Email Retry Options</h3>

                                    <div class="mb-4">
                                        <label for="max_email_retries"
                                            class="block text-sm font-medium text-gray-700">Max Email Retries</label>
                                        <input type="number" name="max_email_retries" id="max_email_retries" min="1"
                                            max="10"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                            value="{{ old('max_email_retries', $settings->max_email_retries) }}"
                                            required>
                                        @error('max_email_retries')
                                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="log_retention_days"
                                            class="block text-sm font-medium text-gray-700">Log Retention (days)</label>
                                        <input type="number" name="log_retention_days" id="log_retention_days" min="1"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                            value="{{ old('log_retention_days', $settings->log_retention_days) }}"
                                            required>
                                        @error('log_retention_days')
                                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="enable_scheduling"
                                                class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
                                                {{ $settings->enable_scheduling ? 'checked' : '' }}>
                                            <span class="ml-2 text-sm text-gray-600">Enable Email Scheduling</span>
                                        </label>
                                        <p class="mt-1 text-xs text-gray-500">When enabled, emails can be scheduled for
                                            later delivery</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SMTP Configuration Tab -->
                        <div id="smtp" class="tab-content hidden">
                            <div class="grid grid-cols-1 gap-6">
                                <div class="col-span-1">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Mail Server Configuration</h3>

                                    <div class="mb-4">
                                        <label for="mail_mailer" class="block text-sm font-medium text-gray-700">Mail
                                            Driver</label>
                                        <select name="mail_mailer" id="mail_mailer"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                            @foreach($mailers as $mailer)
                                            <option value="{{ $mailer }}" {{ $settings->mail_mailer === $mailer ?
                                                'selected' : '' }}>{{ ucfirst($mailer) }}</option>
                                            @endforeach
                                        </select>
                                        @error('mail_mailer')
                                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="mail_host" class="block text-sm font-medium text-gray-700">SMTP
                                            Host</label>
                                        <input type="text" name="mail_host" id="mail_host"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                            value="{{ old('mail_host', $settings->mail_host) }}" required>
                                        @error('mail_host')
                                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="mail_port" class="block text-sm font-medium text-gray-700">SMTP
                                            Port</label>
                                        <input type="number" name="mail_port" id="mail_port"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                            value="{{ old('mail_port', $settings->mail_port) }}" required>
                                        @error('mail_port')
                                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="mail_username" class="block text-sm font-medium text-gray-700">SMTP
                                            Username</label>
                                        <input type="text" name="mail_username" id="mail_username"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                            value="{{ old('mail_username', $settings->mail_username) }}">
                                        @error('mail_username')
                                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="mail_password" class="block text-sm font-medium text-gray-700">SMTP
                                            Password</label>
                                        <input type="password" name="mail_password" id="mail_password"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                            placeholder="{{ $settings->mail_password ? '••••••••' : '' }}">
                                        <p class="mt-1 text-xs text-gray-500">Leave blank to keep the current password
                                        </p>
                                        @error('mail_password')
                                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="mail_encryption"
                                            class="block text-sm font-medium text-gray-700">Encryption</label>
                                        <select name="mail_encryption" id="mail_encryption"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                            <option value="">None</option>
                                            <option value="tls" {{ $settings->mail_encryption === 'tls' ? 'selected' :
                                                '' }}>TLS</option>
                                            <option value="ssl" {{ $settings->mail_encryption === 'ssl' ? 'selected' :
                                                '' }}>SSL</option>
                                        </select>
                                        @error('mail_encryption')
                                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="test_email" class="block text-sm font-medium text-gray-700">Test
                                            Email Address</label>
                                        <input type="email" id="test_email"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                            placeholder="email@example.com">
                                        <p class="mt-1 text-xs text-gray-500">Optional: Enter an email address to send a
                                            test message</p>
                                    </div>

                                    <div class="mt-6 flex space-x-4">
                                        <button type="button" id="test-smtp"
                                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            Test Connection
                                        </button>
                                        {{-- <button type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            Save Settings
                                        </button> --}}
                                        <span id="smtp-test-result" class="ml-3 self-center"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Queue Settings Tab -->
                        <div id="queue" class="tab-content hidden">
                            <div class="grid grid-cols-1 gap-6">
                                <div class="col-span-1">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Queue Configuration</h3>

                                    <div class="mb-4">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="queue_emails"
                                                class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
                                                {{ $settings->queue_emails ? 'checked' : '' }}>
                                            <span class="ml-2 text-sm text-gray-600">Queue Emails</span>
                                        </label>
                                        <p class="mt-1 text-xs text-gray-500">When enabled, emails will be processed in
                                            the background queue</p>
                                    </div>

                                    <div class="mb-4">
                                        <label for="queue_connection"
                                            class="block text-sm font-medium text-gray-700">Queue Connection</label>
                                        <select name="queue_connection" id="queue_connection"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                            @foreach($queueConnections as $connection)
                                            <option value="{{ $connection }}" {{ $settings->queue_connection ===
                                                $connection ? 'selected' : '' }}>{{ ucfirst($connection) }}</option>
                                            @endforeach
                                        </select>
                                        @error('queue_connection')
                                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="queue_name" class="block text-sm font-medium text-gray-700">Queue
                                            Name</label>
                                        <input type="text" name="queue_name" id="queue_name"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                            value="{{ old('queue_name', $settings->queue_name) }}">
                                        @error('queue_name')
                                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mt-6">
                                        {{-- <button type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            Save Settings
                                        </button> --}}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Rate Limiting Tab -->
                        <div id="limits" class="tab-content hidden">
                            <div class="grid grid-cols-1 gap-6">
                                <div class="col-span-1">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Rate Limiting Configuration</h3>

                                    <div class="mb-4">

                                        <p class="mt-1 text-xs text-gray-500">When enabled, email sending will be
                                            limited based on the thresholds below</p>
                                    </div>







                                    <div class="mt-6">
                                        {{-- <button type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            Save Settings
                                        </button> --}}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notification Settings Tab -->
                        <div id="notifications" class="tab-content hidden">
                            <div class="grid grid-cols-1 gap-6">
                                <div class="col-span-1">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Admin Notifications</h3>

                                    <div class="mb-4">

                                        <p class="mt-1 text-xs text-gray-500">When enabled, admins will be notified of
                                            important email system events</p>
                                    </div>

                                    test-smtp

                                    <div class="mt-6">
                                        {{-- <button type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            Save Settings
                                        </button> --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Execute when the DOM is fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Tab functionality
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');

            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');

                    // Hide all tabs and remove active class
                    tabContents.forEach(tab => tab.classList.add('hidden'));
                    tabButtons.forEach(btn => {
                        btn.classList.remove('active', 'border-primary-500', 'text-primary-600');
                        btn.classList.add('border-transparent', 'text-gray-500');
                    });

                    // Show selected tab and add active class
                    document.getElementById(tabId).classList.remove('hidden');
                    this.classList.add('active', 'border-primary-500', 'text-primary-600');
                    this.classList.remove('border-transparent', 'text-gray-500');

                    // Update current tab display in the fixed top bar
                    document.getElementById('current-tab-display').textContent = this.textContent.trim();
                });
            });

            // SMTP Connection Test
            const testSmtpButton = document.getElementById('test-smtp');
            if (testSmtpButton) {
                testSmtpButton.addEventListener('click', function() {
                    const resultElement = document.getElementById('smtp-test-result');
                    resultElement.textContent = 'Testing connection...';
                    resultElement.className = 'ml-3 text-warning-500';

                    // Get form data
                    const data = {
                        mail_mailer: document.getElementById('mail_mailer').value,
                        mail_host: document.getElementById('mail_host').value,
                        mail_port: document.getElementById('mail_port').value,
                        mail_username: document.getElementById('mail_username').value,
                        mail_password: document.getElementById('mail_password').value,
                        mail_encryption: document.getElementById('mail_encryption').value,
                        test_email: document.getElementById('test_email').value
                    };

                    // Create request headers with CSRF token
                    const headers = new Headers({
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    });

                    // Send test request
                    fetch('{{ route('laravel-emails.test-smtp') }}', {
                        method: 'POST',
                        headers: headers,
                        body: JSON.stringify(data)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            resultElement.textContent = data.message;
                            resultElement.className = 'ml-3 text-success-500';
                        } else {
                            // Display user-friendly error
                            resultElement.textContent = data.message;
                            resultElement.className = 'ml-3 text-danger-500';

                            // Create "View Details" button for technical details
                            if (data.details) {
                                const existingDetailsBtn = document.getElementById('error-details-btn');
                                if (existingDetailsBtn) {
                                    existingDetailsBtn.remove();
                                }

                                const detailsBtn = document.createElement('button');
                                detailsBtn.id = 'error-details-btn';
                                detailsBtn.textContent = 'View Technical Details';
                                detailsBtn.className = 'ml-3 text-xs text-gray-500 underline';
                                detailsBtn.onclick = function() {
                                    showErrorDetails(data.details);
                                };
                                resultElement.appendChild(detailsBtn);
                            }
                        }
                    })
                    .catch(error => {
                        resultElement.textContent = 'Connection test failed: Network error';
                        resultElement.className = 'ml-3 text-danger-500';
                    });
                });
            }

            // Function to show technical error details
            function showErrorDetails(errorDetails) {
                // Create modal if it doesn't exist
                let modal = document.getElementById('error-details-modal');
                if (!modal) {
                    modal = document.createElement('div');
                    modal.id = 'error-details-modal';
                    modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50';
                    modal.innerHTML = `
                        <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-[80vh] overflow-y-auto">
                            <div class="border-b px-4 py-3 flex justify-between items-center">
                                <h3 class="text-lg font-medium text-gray-900">Technical Error Details</h3>
                                <button id="close-modal-btn" class="text-gray-400 hover:text-gray-500">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="px-4 py-3">
                                <p class="text-sm text-gray-500 mb-2">This technical information may help with troubleshooting:</p>
                                <pre id="error-details-content" class="bg-gray-100 p-3 rounded text-xs text-danger-600 overflow-x-auto"></pre>
                            </div>
                            <div class="border-t px-4 py-3 flex justify-end">
                                <button id="copy-error-btn" class="mr-3 inline-flex items-center px-3 py-1 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                    Copy to Clipboard
                                </button>
                                <button id="close-error-btn" class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                    Close
                                </button>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(modal);

                    // Add event listeners to close modal
                    document.getElementById('close-modal-btn').addEventListener('click', () => {
                        modal.style.display = 'none';
                    });
                    document.getElementById('close-error-btn').addEventListener('click', () => {
                        modal.style.display = 'none';
                    });

                    // Add copy to clipboard functionality
                    document.getElementById('copy-error-btn').addEventListener('click', () => {
                        const errorText = document.getElementById('error-details-content').textContent;
                        navigator.clipboard.writeText(errorText).then(() => {
                            const copyBtn = document.getElementById('copy-error-btn');
                            const originalText = copyBtn.textContent;
                            copyBtn.textContent = 'Copied!';
                            setTimeout(() => {
                                copyBtn.textContent = originalText;
                            }, 1500);
                        });
                    });
                }

                // Update error details content and show modal
                document.getElementById('error-details-content').textContent = errorDetails;
                modal.style.display = 'flex';
            }

            // Admin emails add/remove functionality
            const addAdminEmailButton = document.getElementById('add-admin-email');
            const adminEmailsContainer = document.getElementById('admin-emails-container');

            if (addAdminEmailButton && adminEmailsContainer) {
                // Add new email field
                addAdminEmailButton.addEventListener('click', function() {
                    const emailField = document.createElement('div');
                    emailField.className = 'flex mb-2';
                    emailField.innerHTML = `
                        <input type="email" name="admin_notification_emails[]" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <button type="button" class="remove-email ml-2 px-2 py-1 bg-danger-500 text-white rounded">Remove</button>
                    `;
                    adminEmailsContainer.appendChild(emailField);

                    // Add event listener to the new remove button
                    const removeButton = emailField.querySelector('.remove-email');
                    removeButton.addEventListener('click', function() {
                        adminEmailsContainer.removeChild(emailField);
                    });
                });

                // Add event listeners to existing remove buttons
                const removeButtons = document.querySelectorAll('.remove-email');
                removeButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const emailField = button.parentElement;
                        adminEmailsContainer.removeChild(emailField);
                    });
                });
            }
        });
    </script>
</x-app-layout>