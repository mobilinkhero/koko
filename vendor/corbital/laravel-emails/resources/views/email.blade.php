{{-- resources/views/vendor/laravel-emails/email.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <style type="text/css">
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #333;
            font-size: 16px;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .email-header {
            margin-bottom: 20px;
        }
        .email-content {
            background-color: #ffffff;
            border-radius: 4px;
            padding: 20px;
        }
        .email-footer {
            margin-top: 20px;
            padding-top: 20px;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="email-container">
        @if(isset($layout) && $layout)
            {{-- If using a layout --}}
            {!! str_replace(
                ['{HEADER}', '{CONTENT}', '{FOOTER}'],
                [$layout->header ?? '', $body, $layout->footer ?? ''],
                $layout->master_template ?? '<div>{CONTENT}</div>'
            ) !!}
        @else
            {{-- Simple layout without a layout model --}}
            <div class="email-header">
                <h2>{{ $title }}</h2>
            </div>
            <div class="email-content">
                {!! $body !!}
            </div>
            <div class="email-footer">
                {{ config('app.name') }} &copy; {{ date('Y') }}
            </div>
        @endif
    </div>
</body>
</html>
