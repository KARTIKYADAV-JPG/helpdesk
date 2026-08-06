<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Reply: {{ $ticket->ticket_number }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #334155;
            background-color: #f8fafc;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            padding: 30px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .header {
            border-bottom: 2px solid #6366f1;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .ticket-num {
            display: inline-block;
            background: #eef2ff;
            color: #4f46e5;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        .subject {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin: 8px 0 0 0;
        }
        .content {
            font-size: 15px;
            color: #1e293b;
            white-space: pre-wrap;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            font-size: 12px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <span class="ticket-num">{{ $ticket->ticket_number }}</span>
            <h1 class="subject">{{ $ticket->subject }}</h1>
        </div>

        <div class="content">
{!! nl2br(e($reply->body)) !!}
        </div>

        <div class="footer">
            <p>This is an automated notification from {{ config('app.name', 'Helpdesk') }}.</p>
            <p>Ticket Reference: <strong>{{ $ticket->ticket_number }}</strong></p>
        </div>
    </div>
</body>
</html>
