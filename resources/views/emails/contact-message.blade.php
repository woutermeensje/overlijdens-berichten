<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nieuw contactbericht</title>
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Work Sans', sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; }
        .header { background-color: #111827; padding: 24px 40px; }
        .header h1 { color: #ffffff; margin: 0; font-size: 18px; }
        .body { padding: 32px 40px; color: #374151; line-height: 1.7; }
        h2 { font-size: 15px; margin: 24px 0 0; }
        table { width: 100%; border-collapse: collapse; margin: 8px 0 16px; }
        td { padding: 10px 12px; border-bottom: 1px solid #e5e7eb; font-size: 14px; }
        .message { white-space: pre-wrap; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px 16px; font-size: 14px; }
        td:first-child { font-weight: 600; color: #6b7280; width: 40%; }
        .footer { background-color: #f9fafb; padding: 16px 40px; text-align: center; font-size: 13px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>Nieuw bericht via het contactformulier</h1>
        </div>
        <div class="body">
            <table>
                <tr>
                    <td>Onderwerp</td>
                    <td>{{ $contact['subject_label'] }}</td>
                </tr>
                <tr>
                    <td>Naam</td>
                    <td>{{ $contact['name'] }}</td>
                </tr>
                <tr>
                    <td>E-mailadres</td>
                    <td><a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a></td>
                </tr>
                @if(! empty($contact['phone']))
                    <tr>
                        <td>Telefoon</td>
                        <td>{{ $contact['phone'] }}</td>
                    </tr>
                @endif
                @if(! empty($contact['notice']))
                    <tr>
                        <td>Over bericht</td>
                        <td>{{ $contact['notice'] }}</td>
                    </tr>
                @endif
            </table>

            <h2>Bericht</h2>
            <div class="message">{{ $contact['message'] }}</div>

            <p>Je kunt deze e-mail direct beantwoorden; je antwoord gaat naar {{ $contact['email'] }}.</p>
        </div>
        <div class="footer">
            <p>Overlijdensberichten.nl – intern bericht</p>
        </div>
    </div>
</body>
</html>
