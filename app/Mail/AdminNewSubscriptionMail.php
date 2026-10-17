<?php

namespace App\Mail;

use App\Models\RegionSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class AdminNewSubscriptionMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, RegionSubscription>  $subscriptions  alle gebieden uit één aanmelding
     */
    public function __construct(public Collection $subscriptions) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nieuwe nieuwsbrief aanmelding',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-new-subscription',
        );
    }
}
