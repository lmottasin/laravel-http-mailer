<?php

declare(strict_types=1);

namespace Lmottasin\HttpMailer\Tests\Fixtures;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

final class WelcomeMail extends Mailable
{
    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Welcome aboard');
    }

    public function content(): Content
    {
        return new Content(htmlString: '<p>Hello from a Mailable</p>');
    }
}
