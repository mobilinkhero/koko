<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $layout->name }} - Layout Preview</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            color: #333;
            background-color: #f4f4f4;
        }
        .preview-header {
            background-color: #fff;
            border-bottom: 1px solid #ddd;
            padding: 15px 20px;
            position: sticky;
            top: 0;
            z-index: 10;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .preview-header h1 {
            margin: 0;
            font-size: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .preview-actions {
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
        }
        .btn-primary {
            background-color: #3498db;
            color: white;
            border: 1px solid #2980b9;
        }
        .btn-secondary {
            background-color: #f8f9fa;
            color: #212529;
            border: 1px solid #ddd;
        }
        .preview-container {
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .email-preview {
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
        }
        .preview-section {
            padding: 15px 20px;
            margin-bottom: 20px;
        }
        .preview-section h2 {
            margin-top: 0;
            font-size: 16px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            color: #555;
        }
        .placeholder-content {
            background-color: #f9f9f9;
            border: 1px dashed #ddd;
            padding: 15px;
            margin: 10px 0;
            text-align: center;
            color: #777;
        }
        .layout-code {
            background-color: #f8f8f8;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 4px;
            font-family: monospace;
            white-space: pre-wrap;
            word-break: break-all;
            font-size: 13px;
            color: #333;
            margin-top: 15px;
            overflow: auto;
            max-height: 300px;
        }
    </style>
</head>
<body>
    <div class="preview-header">
        <h1>
            <span>{{ $layout->name }} Layout Preview</span>
            <div class="preview-actions">
                <a href="{{ route('laravel-emails.layouts.edit', $layout) }}" class="btn btn-primary">Edit Layout</a>
                <a href="{{ route('laravel-emails.layouts.index') }}" class="btn btn-secondary">Back to Layouts</a>
            </div>
        </h1>
    </div>

    <div class="preview-container">
        <div class="preview-section">
            <h2>Full Email Preview</h2>
            <div class="email-preview">
                <iframe id="preview-frame" style="width: 100%; min-height: 600px; border: none;"></iframe>
            </div>
        </div>

        <div class="preview-section">
            <h2>Layout Structure</h2>
            <div>
                <p><strong>Variables:</strong>
                    @if(!empty($layout->variables) && is_array($layout->variables))
                        {{ implode(', ', $layout->variables) }}
                    @else
                        None defined
                    @endif
                </p>
                <p><strong>Status:</strong>
                    @if($layout->is_active)
                        <span style="color: green;">Active</span>
                    @else
                        <span style="color: red;">Inactive</span>
                    @endif

                    @if($layout->is_default)
                        <span style="margin-left: 10px; color: blue;">(Default)</span>
                    @endif

                    @if($layout->is_system)
                        <span style="margin-left: 10px; color: gray;">(System)</span>
                    @endif
                </p>

                <p><strong>Used by {{ $layout->templates()->count() }} template(s)</strong></p>
            </div>

            <div class="layout-code">{{ $layout->master_template }}</div>
        </div>

        <div class="preview-section">
            <h2>Header</h2>
            <div class="layout-code">{{ $layout->header }}</div>
        </div>

        <div class="preview-section">
            <h2>Footer</h2>
            <div class="layout-code">{{ $layout->footer }}</div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const iframe = document.getElementById('preview-frame');
            const content = `{!! str_replace('\'', '\\\'', $previewHtml) !!}`;

            const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
            iframeDoc.open();
            iframeDoc.write(content);
            iframeDoc.close();

            // Adjust iframe height based on content
            iframe.onload = function() {
                iframe.style.height = (iframe.contentWindow.document.body.scrollHeight + 20) + 'px';
            };
        });
    </script>
</body>
</html>
