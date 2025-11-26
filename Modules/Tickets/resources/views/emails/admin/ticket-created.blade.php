<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ t('new_support_ticket') }} - #{{ $ticket->ticket_id }}</title>
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
            border-bottom: 3px solid #dc3545;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #dc3545;
            margin: 0;
            font-size: 28px;
        }

        .urgent-notice {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            border: 1px solid #f5c6cb;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }

        .ticket-info {
            background: #f8f9fa;
            border-left: 4px solid #dc3545;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .client-info {
            background: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
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

        .priority-high {
            color: #dc3545;
            font-weight: bold;
        }

        .priority-medium {
            color: #ffc107;
            font-weight: bold;
        }

        .priority-low {
            color: #28a745;
            font-weight: bold;
        }

        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }

        .button:hover {
            background: #c82333;
        }

        .content {
            background: #fff;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            margin: 20px 0;
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
            <h1>{{ t('new_support_ticket') }}</h1>
            <p>{{ t('new_ticket_requires') }}</p>
        </div>

        @if ($ticket->priority === 'high')
            <div class="urgent-notice">
                <h3 style="margin-top: 0; color: #721c24;">{{ t('high_priority_ticket') }}</h3>
                <p style="margin-bottom: 0; color: #721c24; font-weight: bold;">{{ t('ticket_requires_immediate') }}</p>
            </div>
        @endif

        <p>{{ t('new_support_ticket_submit') }}</p>

        <div class="ticket-info">
            <h3 style="margin-top: 0; color: #dc3545;">{{ t('ticket_details') }}</h3>

            <div class="info-row">
                <span class="info-label">{{ t('ticket_id') }}</span>
                <span class="info-value">#{{ $ticket->ticket_id }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ t('subject_email') }}</span>
                <span class="info-value">{{ $ticket->subject }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ t('priority_email') }}</span>
                <span class="info-value priority-{{ $ticket->priority }}">{{ ucfirst($ticket->priority) }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ t('status_email') }}</span>
                <span class="info-value">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ t('department_email') }}</span>
                <span class="info-value">{{ $ticket->department->name ?? 'General Support' }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ t('created_1') }}</span>
                <span class="info-value">{{ format_date_time($ticket->created_at) }}</span>
            </div>
        </div>

        <div class="client-info">
            <h3 style="margin-top: 0; color: #007bff;">{{ t('client_information') }}</h3>

            <div class="info-row">
                <span class="info-label">{{ t('name_email') }}</span>
                <span class="info-value">{{ $client->name }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ t('email_create') }}</span>
                <span class="info-value">{{ $client->email }}</span>
            </div>

            @if ($client->phone)
                <div class="info-row">
                    <span class="info-label">{{ t(key: 'phone_email') }}</span>
                    <span class="info-value">{{ $client->phone }}</span>
                </div>
            @endif

            <div class="info-row">
                <span class="info-label">{{ t('client_since') }}</span>
                <span class="info-value">{{ $client->created_at->format('M j, Y') }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ t('total_ticket_email') }}</span>
                <span class="info-value">{{ $client->tickets()->count() }}</span>
            </div>
        </div>

        @if ($ticket->body)
            <div class="content">
                <h4 style="margin-top: 0; color: #495057;">{{ t(key: 'client_message') }}</h4>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 3px solid #6c757d;">
                    {{ $ticket->body }}
                </div>
            </div>
        @endif

        @if ($ticket->attachments && count($ticket->attachments) > 0)
            <div class="content">
                <h4 style="margin-top: 0; color: #495057;">{{ t('attachments') }} ({{ count($ticket->attachments) }})
                </h4>
                <ul style="margin: 10px 0; color: #6c757d;">
                    @foreach ($ticket->attachments as $attachment)
                        <li>
                            <strong>{{ $attachment['original_name'] ?? $attachment['filename'] }}</strong>
                            @if (isset($attachment['size']))
                                <span
                                    style="color: #868e96; font-size: 0.9em;">({{ number_format($attachment['size'] / 1024, 1) }}
                                    KB)</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $admin_url }}" class="button">{{ t('review_ticket_admin_panel') }}</a>
        </div>

        <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; border: 1px solid #ffeaa7;">
            <p style="margin: 0; color: #856404;"><strong>{{ t('recommended_actions') }}</strong></p>
            <ul style="margin: 10px 0; color: #856404;">
                <li>{{ t('review_ticket_client_information') }}</li>
                <li>{{ t('assign_appropriate_member') }}</li>
                <li>{{ t('update_priority_necessary') }}</li>
                <li>{{ t('respond_the_client_promptly') }}</li>
                @if ($ticket->priority === 'high')
                    <li><strong>{{ t('address_high_priority_ticket') }}</strong></li>
                @endif
            </ul>
        </div>

        <div class="footer">
            <p>{{ t('admin_support_system_automated') }}</p>
            <p>{{ t('email_send_to_notify') }}</p>
        </div>
    </div>
</body>

</html>
