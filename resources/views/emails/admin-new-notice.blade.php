<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nieuw bericht geplaatst</title>
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
        td:first-child { font-weight: 600; color: #6b7280; width: 40%; }
        .footer { background-color: #f9fafb; padding: 16px 40px; text-align: center; font-size: 13px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>Nieuw bericht geplaatst</h1>
        </div>
        <div class="body">
            <p>Er is een nieuw bericht geplaatst via het formulier op de website. Het staat direct online.</p>

            <h2>Bericht</h2>
            <table>
                <tr>
                    <td>Overledene</td>
                    <td>{{ $notice->title }}</td>
                </tr>
                <tr>
                    <td>Type</td>
                    <td>{{ \App\Models\MemorialNotice::typeLabels()[$notice->type] ?? ucfirst($notice->type) }}</td>
                </tr>
                @if($notice->city)
                    <tr>
                        <td>Plaats</td>
                        <td>{{ $notice->city }}</td>
                    </tr>
                @endif
                <tr>
                    <td>Bekijken</td>
                    <td><a href="{{ route('notice.show', $notice->slug) }}">{{ route('notice.show', $notice->slug) }}</a></td>
                </tr>
                <tr>
                    <td>Beheren</td>
                    <td><a href="{{ route('account.notices.edit', $notice) }}">Bewerken in beheeromgeving</a></td>
                </tr>
                <tr>
                    <td>Geplaatst op</td>
                    <td>{{ $notice->created_at->timezone('Europe/Amsterdam')->format('d-m-Y \o\m H:i') }}</td>
                </tr>
            </table>

            <h2>Contactgegevens plaatser</h2>
            <table>
                <tr>
                    <td>Naam</td>
                    <td>{{ $notice->placer_name }}</td>
                </tr>
                <tr>
                    <td>E-mailadres</td>
                    <td>{{ $notice->placer_email }}</td>
                </tr>
                @if($notice->placer_phone)
                    <tr>
                        <td>Telefoon</td>
                        <td>{{ $notice->placer_phone }}</td>
                    </tr>
                @endif
            </table>
        </div>
        <div class="footer">
            <p>Overlijdensberichten.nl – intern bericht</p>
        </div>
    </div>
</body>
</html>
