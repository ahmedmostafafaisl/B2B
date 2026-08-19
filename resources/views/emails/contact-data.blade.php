<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body  { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .card { background: #fff; border-radius: 8px; padding: 30px; max-width: 600px; margin: auto; }
        h2    { color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        td    { padding: 10px 12px; border-bottom: 1px solid #eee; }
        td:first-child { font-weight: bold; color: #555; width: 35%; }
        .status { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 13px; background: #e0f0ff; color: #0066cc; }
        .note-box     { background: #FFC7BB; border-left: 4px solid #FE4A23; padding: 12px 16px; margin-top: 20px; border-radius: 4px; }
        .url-box      { background: #f0f8ff; border-left: 4px solid #4090d0; padding: 12px 16px; margin-top: 12px; border-radius: 4px; }
        .note-box strong, .url-box strong { display: block; margin-bottom: 6px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Contact Details</h2>

        <table>
            <tr><td>Name</td><td>{{ $contact['name'] ?? '-' }}</td></tr>
            <tr><td>Email</td><td>{{ $contact['email'] ?? '-' }}</td></tr>
            <tr><td>Phone</td><td>{{ $contact['phone'] ?? '-' }}</td></tr>
            <tr><td>Subject</td><td>{{ $contact['subject'] ?? '-' }}</td></tr>
            <tr><td>Status</td><td><span class="status">{{ $contact['status'] ?? '-' }}</span></td></tr>
            <tr><td>Message</td><td>{{ $contact['message'] ?? '-' }}</td></tr>
            @if(!empty($contact['note']))
                <tr><td>Last Employee's Note</td><td>{{ $contact['note'] }}</td></tr>
            @endif
        </table>

        @if(!empty($contact['email_note']))
            <div class="note-box">
                <strong>Reminder</strong>
                {{ $contact['email_note'] }}
            </div>
        @endif

        @if(!empty($contact['contact_url']))
            <div class="url-box">
                <strong>Contact Link</strong>
                <a href="{{ $contact['contact_url'] }}">{{ $contact['contact_url'] }}</a>
            </div>
        @endif
    </div>
</body>
</html>
