<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aanmelding bevestigd</title>
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Work Sans', sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; }
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
            <p>Bedankt voor je aanmelding. Je ontvangt voortaan wekelijks een overzicht van de nieuwste overlijdensberichten.</p>

            <div class="highlight">
                @foreach($subscriptions as $subscription)
                    {{ ucfirst($subscription->areaLabel()) }}@if(! $loop->last)<br>@endif
                @endforeach
            </div>

            <p>Elke week sturen we je een e-mail met de meest recente overlijdensberichten uit {{ $subscriptions->count() === 1 ? 'dit gebied' : 'deze gebieden' }}.</p>

            <p>Wil je je later afmelden? Dat kan per gebied:</p>

            @foreach($subscriptions as $subscription)
                <p><a href="{{ route('newsletter.unsubscribe', $subscription->token) }}" class="btn">Afmelden voor {{ $subscription->areaLabel() }}</a></p>
            @endforeach
        </div>
        <div class="footer">
            <p>Je ontvangt deze e-mail omdat je je hebt aangemeld op <a href="{{ url('/') }}">overlijdens-berichten.nl</a>.</p>
        </div>
    </div>
</body>
</html>
