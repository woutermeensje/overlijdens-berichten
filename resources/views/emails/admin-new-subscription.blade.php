<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nieuwe aanmelding</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background-color: #111827; padding: 24px 40px; }
        .header h1 { color: #ffffff; margin: 0; font-size: 18px; }
        .body { padding: 32px 40px; color: #374151; line-height: 1.7; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        td { padding: 10px 12px; border-bottom: 1px solid #e5e7eb; font-size: 14px; }
        td:first-child { font-weight: 600; color: #6b7280; width: 40%; }
        .footer { background-color: #f9fafb; padding: 16px 40px; text-align: center; font-size: 13px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>Nieuwe nieuwsbrief aanmelding</h1>
        </div>
        <div class="body">
            <p>Er is een nieuwe aanmelding binnengekomen via overlijdens-berichten.nl.</p>
            <table>
                <tr>
                    <td>E-mailadres</td>
                    <td>{{ $subscription->email }}</td>
                </tr>
                <tr>
                    <td>Regio / stad</td>
                    <td>{{ $subscription->region ?: 'Heel Nederland' }}</td>
                </tr>
                <tr>
                    <td>Aangemeld op</td>
                    <td>{{ $subscription->created_at->format('d-m-Y \o\m H:i') }}</td>
                </tr>
            </table>
        </div>
        <div class="footer">
            <p>Overlijdensberichten.nl – intern bericht</p>
        </div>
    </div>
</body>
</html>
