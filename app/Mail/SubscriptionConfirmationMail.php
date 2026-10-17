<?php

namespace App\Mail;

use App\Models\RegionSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SubscriptionConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, RegionSubscription>  $subscriptions  alle gebieden uit één aanmelding
     */
    public function __construct(public Collection $subscriptions) {}

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
