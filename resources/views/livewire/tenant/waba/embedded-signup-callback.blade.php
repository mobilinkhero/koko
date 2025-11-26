<!DOCTYPE html>
<html>
<head>
    <title>Connecting...</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: #f5f5f5;
        }
        .container {
            text-align: center;
            padding: 20px;
        }
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #1877F2;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="spinner"></div>
        <p>Connecting your WhatsApp Business Account...</p>
    </div>
    
    <script>
        // Send message to parent window
        if (window.opener) {
            window.opener.postMessage({
                type: 'facebook_embedded_signup_callback',
                @if($code)
                code: '{{ $code }}'
                @elseif($error)
                error: '{{ $error }}',
                error_description: '{{ $error_description ?? "Unknown error" }}'
                @endif
            }, window.location.origin);
            
            // Close popup after a short delay
            setTimeout(function() {
                window.close();
            }, 500);
        } else {
            // Not in popup, redirect normally
            window.location.href = '{{ tenant_route("tenant.connect") }}';
        }
    </script>
</body>
</html>

