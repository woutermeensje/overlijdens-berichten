<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nieuwe organisatie aangemeld</title>
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
            <h1>Nieuwe organisatie aangemeld</h1>
        </div>
        <div class="body">
            <p>Er is een organisatie aangemeld via /organisatie-toevoegen. De vermelding staat direct in het overzicht op <a href="{{ route('organizations.index') }}">{{ route('organizations.index') }}</a>.</p>
            <table>
                <tr>
                    <td>Bedrijfsnaam</td>
                    <td>{{ $organization->name }}</td>
                </tr>
                <tr>
                    <td>Categorie</td>
                    <td>{{ $organization->categoryLabel() }}</td>
                </tr>
                <tr>
                    <td>Plaats</td>
                    <td>{{ $organization->city }}@if($organization->province) ({{ $organization->province }})@endif</td>
                </tr>
                @if($organization->address)
                    <tr>
                        <td>Adres</td>
                        <td>{{ $organization->address }}</td>
                    </tr>
                @endif
                <tr>
                    <td>E-mailadres</td>
                    <td><a href="mailto:{{ $organization->email }}">{{ $organization->email }}</a></td>
                </tr>
                @if($organization->phone)
                    <tr>
                        <td>Telefoon</td>
                        <td>{{ $organization->phone }}</td>
                    </tr>
                @endif
                @if($organization->website)
                    <tr>
                        <td>Website</td>
                        <td><a href="{{ $organization->website }}">{{ $organization->website }}</a></td>
                    </tr>
                @endif
                <tr>
                    <td>Aangemeld op</td>
                    <td>{{ $organization->created_at->timezone('Europe/Amsterdam')->format('d-m-Y \o\m H:i') }}</td>
                </tr>
            </table>

            <h2>Over de organisatie</h2>
            <div class="message">{{ $organization->about }}</div>
        </div>
        <div class="footer">
            <p>Overlijdensberichten.nl – intern bericht</p>
        </div>
    </div>
</body>
</html>
