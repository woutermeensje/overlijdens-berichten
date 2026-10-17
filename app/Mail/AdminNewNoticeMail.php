<?php

namespace App\Mail;

use App\Models\MemorialNotice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminNewNoticeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public MemorialNotice $notice) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nieuw bericht geplaatst: '.$this->notice->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-new-notice',
        );
    }
}
