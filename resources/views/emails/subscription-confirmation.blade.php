<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aanmelding bevestigd</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background-color: #1a56db; padding: 32px 40px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; }
        .body { padding: 32px 40px; color: #374151; line-height: 1.7; }
        .body h2 { font-size: 18px; color: #111827; margin-top: 0; }
        .highlight { background-color: #eff6ff; border-left: 4px solid #1a56db; padding: 12px 16px; border-radius: 4px; margin: 20px 0; font-weight: 600; color: #1e40af; }
        .footer { background-color: #f9fafb; padding: 20px 40px; text-align: center; font-size: 13px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
        .footer a { color: #6b7280; }
        .btn { display: inline-block; background-color: #1a56db; color: #ffffff !important; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 600; margin: 16px 0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>Overlijdensberichten.nl</h1>
        </div>
        <div class="body">
            <h2>Aanmelding bevestigd</h2>
            <p>Bedankt voor uw aanmelding. U ontvangt voortaan wekelijks een overzicht van de nieuwste overlijdensberichten.</p>

            <div class="highlight">
                @if($subscription->region)
                    Regio: {{ $subscription->region }}
                @else
                    Heel Nederland
                @endif
            </div>

            <p>Elke week sturen wij u een e-mail met de meest recente overlijdensberichten
                @if($subscription->region)
                    in en rondom <strong>{{ $subscription->region }}</strong>.
                @else
                    uit heel <strong>Nederland</strong>.
                @endif
            </p>

            <p>Wilt u zich op een later moment afmelden? Dat kan eenvoudig via de knop hieronder.</p>

            <a href="{{ route('newsletter.unsubscribe', $subscription->token) }}" class="btn">Afmelden</a>
        </div>
        <div class="footer">
            <p>U ontvangt deze e-mail omdat u zich heeft aangemeld op <a href="{{ url('/') }}">overlijdens-berichten.nl</a>.</p>
            <p><a href="{{ route('newsletter.unsubscribe', $subscription->token) }}">Uitschrijven</a></p>
        </div>
    </div>
</body>
</html>
