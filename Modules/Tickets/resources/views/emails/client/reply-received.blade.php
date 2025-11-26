<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Reply - Ticket #{{ $ticket->ticket_id }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }

        .email-container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #28a745;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #28a745;
            margin: 0;
            font-size: 28px;
        }

        .ticket-info {
            background: #f8f9fa;
            border-left: 4px solid #28a745;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .reply-content {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }

        .reply-header {
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .info-label {
            font-weight: bold;
            color: #495057;
        }

        .info-value {
            color: #6c757d;
        }

        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }

        .button:hover {
            background: #218838;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="header">
            <h1>{{ t('new_reply_received') }}</h1>
            <p>{{ t('support_ticket_has_been_updated') }}</p>
        </div>

        <p>{{ t('dear') }} {{ $client->name }},</p>

        <p>{{ t('email_description') }}</p>

        <div class="ticket-info">
            <h3 style="margin-top: 0; color: #28a745;">{{ t('ticket_information') }}</h3>

            <div class="info-row">
                <span class="info-label">{{ t('ticket_id') }}</span>
                <span class="info-value">#{{ $ticket->ticket_id }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ t('subject') }}:</span>
                <span class="info-value">{{ $ticket->subject }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ t('status') }}:</span>
                <span class="info-value">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ t('reply_date') }}</span>
                <span class="info-value">{{ format_date_time($reply->created_at) }}</span>
            </div>
        </div>

        <div class="reply-content">
            <div class="reply-header">
                <h4 style="margin: 0; color: #495057;">Latest Reply from {{ $reply->user->name }}</h4>
                <small style="color: #6c757d;">{{ format_date_time($reply->created_at) }}</small>
            </div>

            <div style="color: #495057; line-height: 1.6;">
                {{ Str::limit($reply->message, 200) }}
                @if (strlen($reply->message) > 200)
                    <br><em style="color: #6c757d;">... {{ t('view_full_reply_online') }}</em>
                @endif
            </div>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $login_url }}" class="button">{{ t('view_full_conversation') }}</a>
        </div>

        <div style="background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0; border: 1px solid #c3e6cb;">
            <p style="margin: 0; color: #155724;"><strong>{{ t('quick_actions') }}</strong></p>
            <ul style="margin: 10px 0; color: #155724;">
                <li>{{ t('reply_to_continue_the_conversation') }}</li>
                <li>{{ t('additional_information_if_needed') }}</li>
                <li>{{ t('upload_new_attachments_required') }}</li>
                <li>{{ t('close_ticket_if_issue_resolved') }}</li>
            </ul>
        </div>

        <div class="footer">
            <p>{{ t('this_is_automated_message') }}</p>
            <p>{{ t('log_to_your_account') }}</p>
        </div>
    </div>
</body>

</html>
