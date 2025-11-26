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
        console.log('[Embedded Signup Callback] Page loaded');
        console.log('[Embedded Signup Callback] Has opener:', !!window.opener);
        console.log('[Embedded Signup Callback] Window origin:', window.location.origin);
        @if($code)
        console.log('[Embedded Signup Callback] Code received:', '{{ $code }}');
        @elseif($error)
        console.log('[Embedded Signup Callback] Error received:', '{{ $error }}', '{{ $error_description ?? "Unknown error" }}');
        @endif
        
        // Send message to parent window
        if (window.opener) {
            console.log('[Embedded Signup Callback] Sending message to parent window...');
            const messageData = {
                type: 'facebook_embedded_signup_callback',
                @if($code)
                code: '{{ $code }}'
                @elseif($error)
                error: '{{ $error }}',
                error_description: '{{ $error_description ?? "Unknown error" }}'
                @endif
            };
            console.log('[Embedded Signup Callback] Message data:', messageData);
            console.log('[Embedded Signup Callback] Target origin:', window.location.origin);
            
            try {
                window.opener.postMessage(messageData, window.location.origin);
                console.log('[Embedded Signup Callback] Message sent successfully');
            } catch (error) {
                console.error('[Embedded Signup Callback] Error sending message:', error);
            }
            
            // Close popup after a short delay
            setTimeout(function() {
                console.log('[Embedded Signup Callback] Closing popup...');
                window.close();
            }, 1000);
        } else {
            console.log('[Embedded Signup Callback] No opener found, redirecting normally');
            // Not in popup, redirect normally
            window.location.href = '{{ tenant_route("tenant.connect") }}';
        }
    </script>
</body>
</html>

