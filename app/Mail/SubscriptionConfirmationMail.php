<?php

namespace App\Mail;

use App\Models\RegionSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public RegionSubscription $subscription) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Aanmelding bevestigd – wekelijkse overlijdensberichten',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription-confirmation',
        );
    }
}
