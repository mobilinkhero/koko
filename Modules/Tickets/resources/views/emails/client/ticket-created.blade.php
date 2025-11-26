<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Created - #{{ $ticket->ticket_id }}</title>
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
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 28px;
        }
        .ticket-info {
            background: #f8f9fa;
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
        .priority-high { color: #dc3545; font-weight: bold; }
        .priority-medium { color: #ffc107; font-weight: bold; }
        .priority-low { color: #28a745; font-weight: bold; }
        .status-open { color: #007bff; font-weight: bold; }
        .status-pending { color: #ffc107; font-weight: bold; }
        .status-closed { color: #6c757d; font-weight: bold; }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }
        .button:hover {
            background: #0056b3;
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
            <h1>{{ t('support_ticket_created') }}</h1>
            <p>{{ t('your_support_request_received') }}</p>
        </div>

        <p>{{ t('dear') }} {{ $client->name }},</p>

        <p>{{ t('thank_you_for_contacting_support_team') }}</p>

        <div class="ticket-info">
            <h3 style="margin-top: 0; color: #007bff;">{{ t('ticket_details') }}</h3>

            <div class="info-row">
                <span class="info-label">{{ t('ticket_id') }}</span>
                <span class="info-value">#{{ $ticket->ticket_id }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ t('subject') }}:</span>
                <span class="info-value">{{ $ticket->subject }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ t('priority') }}:</span>
                <span class="info-value priority-{{ $ticket->priority }}">{{ ucfirst($ticket->priority) }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ t('status') }}:</span>
                <span class="info-value status-{{ $ticket->status }}">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ t('department') }}:</span>
                <span class="info-value">{{ $ticket->department->name ?? 'General Support' }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ t('created') }}:</span>
                <span class="info-value">{{ format_date_time($ticket->created_at) }}</span>
            </div>
        </div>

        @if($ticket->body)
            <div class="content">
                <h4 style="margin-top: 0; color: #495057;">{{ t('your_message') }}</h4>
                <p>{{ $ticket->body }}</p>
            </div>
        @endif

        @if($ticket->attachments && count($ticket->attachments) > 0)
            <div class="content">
                <h4 style="margin-top: 0; color: #495057;">{{ t('attachments') }}</h4>
                <ul>
                    @foreach($ticket->attachments as $attachment)
                    <li>{{ $attachment['original_name'] ?? $attachment['filename'] }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $login_url }}" class="button">{{ t('view_ticket') }}</a>
        </div>

        <div style="background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p style="margin: 0; color: #0066cc;"><strong>{{ t('what_happens_next') }}</strong></p>
            <ul style="margin: 10px 0; color: #495057;">
                <li>{{ t('our_support_team_review_ticket') }}</li>
                <li>{{ t('you_receive_updates_via_email') }}</li>
                <li>{{ t('you_can_check_status_anytime') }}</li>
                <li>{{ t('please_keep_your_ticket_id') }}</li>
            </ul>
        </div>

        <div class="footer">
            <p>{{ t('this_is_automated_message') }}</p>
            <p>{{ t('if_you_need_immediate_assistance') }}</p>
        </div>
    </div>
</body>
</html>
