<div class="mb-6">
    <h2 class="text-lg font-medium text-gray-800 mb-2">SMTP Settings</h2>
    <div class="bg-white p-4 rounded border border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="mail_mailer" class="block text-sm font-medium text-gray-700 mb-1">Mail Driver</label>
                <select name="mail_mailer" id="mail_mailer" class="w-full rounded-md border-gray-300">
                    <option value="smtp" {{ $settings->mail_mailer === 'smtp' ? 'selected' : '' }}>SMTP</option>
                    <option value="sendmail" {{ $settings->mail_mailer === 'sendmail' ? 'selected' : '' }}>Sendmail
                    </option>
                    <option value="mailgun" {{ $settings->mail_mailer === 'mailgun' ? 'selected' : '' }}>Mailgun
                    </option>
                    <option value="ses" {{ $settings->mail_mailer === 'ses' ? 'selected' : '' }}>Amazon SES</option>
                    <option value="log" {{ $settings->mail_mailer === 'log' ? 'selected' : '' }}>Log</option>
                </select>
            </div>

            <div>
                <label for="mail_host" class="block text-sm font-medium text-gray-700 mb-1">SMTP Host</label>
                <input type="text" name="mail_host" id="mail_host" value="{{ $settings->mail_host }}"
                    class="w-full rounded-md border-gray-300">
            </div>

            <div>
                <label for="mail_port" class="block text-sm font-medium text-gray-700 mb-1">SMTP Port</label>
                <input type="number" name="mail_port" id="mail_port" value="{{ $settings->mail_port }}"
                    class="w-full rounded-md border-gray-300">
            </div>

            <div>
                <label for="mail_encryption" class="block text-sm font-medium text-gray-700 mb-1">Encryption</label>
                <select name="mail_encryption" id="mail_encryption" class="w-full rounded-md border-gray-300">
                    <option value="tls" {{ $settings->mail_encryption === 'tls' ? 'selected' : '' }}>TLS</option>
                    <option value="ssl" {{ $settings->mail_encryption === 'ssl' ? 'selected' : '' }}>SSL</option>
                    <option value="" {{ $settings->mail_encryption === '' ? 'selected' : '' }}>None</option>
                </select>
            </div>

            <div>
                <label for="mail_username" class="block text-sm font-medium text-gray-700 mb-1">SMTP Username</label>
                <input type="text" name="mail_username" id="mail_username" value="{{ $settings->mail_username }}"
                    class="w-full rounded-md border-gray-300">
            </div>

            <div>
                <label for="mail_password" class="block text-sm font-medium text-gray-700 mb-1">SMTP Password</label>
                <input type="password" name="mail_password" id="mail_password" value="{{ $settings->mail_password }}"
                    class="w-full rounded-md border-gray-300">
            </div>
        </div>
    </div>
</div>
<div class="mt-4">
    <button type="button" id="test-smtp" class="px-4 py-2 bg-info-600 text-white rounded hover:bg-info-700 transition">
        Test SMTP Connection
    </button>
    <div id="smtp-test-result" class="mt-2"></div>
</div>

<script>
    document.getElementById('test-smtp').addEventListener('click', function() {
        // Show loading
        document.getElementById('smtp-test-result').innerHTML = '<div class="text-info-600">Testing connection...</div>';
        
        // Get form data
        const formData = new FormData();
        formData.append('mail_mailer', document.getElementById('mail_mailer').value);
        formData.append('mail_host', document.getElementById('mail_host').value);
        formData.append('mail_port', document.getElementById('mail_port').value);
        formData.append('mail_username', document.getElementById('mail_username').value);
        formData.append('mail_password', document.getElementById('mail_password').value);
        formData.append('mail_encryption', document.getElementById('mail_encryption').value);
        
        // Send test request
        fetch('/admin/emails/test-smtp', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('smtp-test-result').innerHTML = 
                    '<div class="text-success-600">Connection successful!</div>';
            } else {
                document.getElementById('smtp-test-result').innerHTML = 
                    `<div class="text-danger-600">Connection failed: ${data.message}</div>`;
            }
        })
        .catch(error => {
            document.getElementById('smtp-test-result').innerHTML = 
                `<div class="text-danger-600">Error: ${error.message}</div>`;
        });
    });
</script>