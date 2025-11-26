<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ (' ticket_status_updated ') }}- #{{ $ticket->ticket_id }}</title>
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
            border-bottom: 3px solid #ffc107;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #ffc107;
            margin: 0;
            font-size: 28px;
        }
        .status-change {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 25px;
            margin: 25px 0;
            text-align: center;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
            margin: 0 10px;
        }
        .status-open { background: #d1ecf1; color: #0c5460; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-answered { background: #d4edda; color: #155724; }
        .status-closed { background: #f8d7da; color: #721c24; }
        .status-on_hold { background: #e2e3e5; color: #383d41; }
        .arrow {
            font-size: 24px;
            color: #ffc107;
            margin: 0 15px;
        }
        .ticket-info {
            background: #f8f9fa;
            border-left: 4px solid #ffc107;
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
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #ffc107;
            color: #212529;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }
        .button:hover {
            background: #e0a800;
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
            <h1>{{ t('ticket_status_updated') }}</h1>
            <p>{{ t('support_ticket_status_changed') }}</p>
        </div>

        <p>{{ t('dear') }}{{ $client->name }},</p>

        <p>{{ t('status_of_support_ticket_has_updated') }}</p>

        <div class="status-change">
            <h3 style="margin-top: 0; color: #856404;">{{ t('status_change') }}</h3>
            <div style="display: flex; align-items: center; justify-content: center; flex-wrap: wrap;">
                <span class="status-badge status-{{ $old_status }}">{{ ucfirst(str_replace('_', ' ', $old_status)) }}</span>
                <span class="arrow">→</span>
                <span class="status-badge status-{{ $new_status }}">{{ ucfirst(str_replace('_', ' ', $new_status)) }}</span>
            </div>
            <p style="margin-bottom: 0; color: #856404; font-size: 14px; margin-top: 15px;">
                <strong>{{ t('updated') }}</strong> {{ now()->format('M j, Y \a\t g:i A') }}
            </p>
        </div>

        <div class="ticket-info">
            <h3 style="margin-top: 0; color: #ffc107;">{{ t('ticket_information') }}</h3>

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
                <span class="info-value">{{ ucfirst($ticket->priority) }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ t('department') }}:</span>
                <span class="info-value">{{ $ticket->department->name ?? 'General Support' }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ t('last_updated') }}</span>
                <span class="info-value">{{  format_date_time($ticket->updated_at) }}</span>
            </div>
        </div>

        @if($new_status === 'answered')
            <div style="background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0; border: 1px solid #c3e6cb;">
                <p style="margin: 0; color: #155724;"><strong>{{ t('your_ticket_has_been_answered') }}</strong></p>
                <p style="margin: 10px 0 0 0; color: #155724;">{{ t('log_view_response_continue') }}</p>
            </div>
        @elseif($new_status === 'closed')
            <div style="background: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px 0; border: 1px solid #f5c6cb;">
                <p style="margin: 0; color: #721c24;"><strong>{{ t('your_ticket_has_been_closed') }}</strong></p>
                <p style="margin: 10px 0 0 0; color: #721c24;">{{ t('further_assistance_reopen_ticket') }}</p>
            </div>
        @elseif($new_status === 'pending')
            <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; border: 1px solid #ffeaa7;">
                <p style="margin: 0; color: #856404;"><strong>{{ t('your_ticket_is_pending_review') }}</strong></p>
                <p style="margin: 10px 0 0 0; color: #856404;">{{ t('email_respond') }}</p>
            </div>
        @endif

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $login_url }}" class="button">{{ t('view_ticket_details') }}</a>
        </div>

        <div class="footer">
            <p>{{ t('this_is_automated_message') }}</p>
            <p>{{ t('automated_mail_description') }}</p>
        </div>
    </div>
</body>
</html>
