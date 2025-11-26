<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ t('new_client_reply_ticket') }} #{{ $ticket->ticket_id }}</title>
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
            border-bottom: 3px solid #17a2b8;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #17a2b8;
            margin: 0;
            font-size: 28px;
        }

        .ticket-info {
            background: #f8f9fa;
            border-left: 4px solid #17a2b8;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .reply-content {
            background: #e7f7ff;
            border: 1px solid #b8e6ff;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }

        .reply-header {
            border-bottom: 1px solid #b8e6ff;
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
            background: #17a2b8;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }

        .button:hover {
            background: #138496;
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
            <h1>{{ t('new_client_reply') }}</h1>
            <p>{{ t('a_client_has_responded') }}</p>
        </div>

        <p>{{ t('client_reply_description') }}</p>

        <div class="ticket-info">
            <h3 style="margin-top: 0; color: #17a2b8;">{{ t('ticket_information') }}</h3>

            <div class="info-row">
                <span class="info-label">{{ t('ticket_id') }}</span>
                <span class="info-value">#{{ $ticket->ticket_id }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ t('subject_email') }}</span>
                <span class="info-value">{{ $ticket->subject }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ t('client') }}</span>
                <span class="info-value">{{ $client->name }} ({{ $client->email }})</span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ t('priority_email') }}</span>
                <span class="info-value">{{ ucfirst($ticket->priority) }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ t('status_email') }}</span>
                <span class="info-value">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ t('reply_date') }}</span>
                <span class="info-value">{{ format_date_time($reply->created_at) }}</span>
            </div>
        </div>

        <div class="reply-content">
            <div class="reply-header">
                <h4 style="margin: 0; color: #0c5460;">{{ t('latest_reply_from') }} {{ $reply->user->name }}</h4>
                <small style="color: #6c757d;">{{ format_date_time($reply->created_at) }}</small>
            </div>

            <div style="color: #0c5460; line-height: 1.6; background: white; padding: 15px; border-radius: 5px;">
                {{ $reply->message }}
            </div>

            @if ($reply->attachments && count($reply->attachments) > 0)
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #b8e6ff;">
                    <p style="margin: 0 0 10px 0; color: #0c5460; font-weight: bold;">{{ t('attachments') }}</p>
                    <ul style="margin: 0; color: #0c5460;">
                        @foreach ($reply->attachments as $attachment)
                            <li>{{ $attachment['original_name'] ?? $attachment['filename'] }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $admin_url }}" class="button">{{ t('reply_to_client') }}</a>
        </div>

        <div style="background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 20px 0; border: 1px solid #bee5eb;">
            <p style="margin: 0; color: #0c5460;"><strong>{{ t('quick_response_tips') }}</strong></p>
            <ul style="margin: 10px 0; color: #0c5460;">
                <li>{{ t('review_client_response') }}</li>
                <li>{{ t('check_additional_information') }}</li>
                <li>{{ t('update_ticket_status_appropriate') }}</li>
                <li>{{ t('provide_clear_helpful_responses') }}</li>
                <li>{{ t('set_realistic_expectations') }}</li>
            </ul>
        </div>

        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h4 style="margin-top: 0; color: #495057;">{{ t('client_history_summary') }}</h4>
            <ul style="margin: 10px 0; color: #6c757d;">
                <li><strong>{{ t('total_ticket_email') }}</strong> {{ $client->tickets()->count() }}</li>
                <li><strong>{{ t('open_tickets_email') }}</strong>
                    {{ $client->tickets()->whereIn('status', ['open', 'pending', 'answered'])->count() }}</li>
                <li><strong>{{ t('client_since') }}</strong> {{ $client->created_at->format('M j, Y') }}</li>
                <li><strong>{{ t('last_activity_email') }}</strong>
                    {{ $client->tickets()->latest('updated_at')->first()?->updated_at?->format('M j, Y') ?? 'N/A' }}
                </li>
            </ul>
        </div>

        <div class="footer">
            <p>{{ t('admin_support_system') }}</p>
            <p>{{ t('email_notify') }}</p>
        </div>
    </div>
</body>

</html>
