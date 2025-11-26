<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ t('ticket_closed') }} - #{{ $ticket->ticket_id }}</title>
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
            border-bottom: 3px solid #6c757d;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #6c757d;
            margin: 0;
            font-size: 28px;
        }
        .ticket-info {
            background: #f8f9fa;
            border-left: 4px solid #6c757d;
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
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }
        .button:hover {
            background: #5a6268;
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
            <h1>{{ t('ticket_closed') }}</h1>
            <p>{{ t('support_ticket_resolved') }}</p>
        </div>

        <p>{{ t('dear') }} {{ $client->name }},</p>

        <p>{{ t('your_support_ticket_has_been_closed') }}</p>

        <div class="ticket-info">
            <h3 style="margin-top: 0; color: #6c757d;">{{ t('final_ticket_summary') }}</h3>

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
                <span class="info-value">{{ t('closed') }}:</span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ t('department') }}:</span>
                <span class="info-value">{{ $ticket->department->name ?? 'General Support' }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ t('created') }}:</span>
                <span class="info-value">{{ format_date_time($ticket->created_at)  }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">{{ t('closed') }}:</span>
                <span class="info-value">{{ format_date_time($ticket->updated_at)  }}</span>
            </div>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $login_url }}" class="button">{{ t('view_ticket_history') }}</a>
        </div>

        <div style="background: #e9ecef; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p style="margin: 0; color: #495057;"><strong>{{ t('need_more_help') }}</strong></p>
            <ul style="margin: 10px 0; color: #495057;">
                <li>{{ t('reopen_this_ticket_issue_persists') }}</li>
                <li>{{ t('create_new_ticket_different') }}</li>
                <li>{{ t('browse_our_knowledge') }}</li>
                <li>{{ t('contact_our_support_team_directly') }}</li>
            </ul>
        </div>

        <div style="background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 20px 0; border: 1px solid #bee5eb;">
            <p style="margin: 0; color: #0c5460;"><strong>{{ t('we_value_your_feedback') }}</strong></p>
            <p style="margin: 10px 0 0 0; color: #0c5460;">{{ t('if_you_have_moment_appreciate') }}</p>
        </div>

        <div class="footer">
            <p>{{ t('thank_you_for_using_support_services') }}</p>
            <p>{{ t('this_is_automated_message') }}</p>
        </div>
    </div>
</body>
</html>
