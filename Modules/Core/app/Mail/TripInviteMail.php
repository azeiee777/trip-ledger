<?php

namespace Modules\Core\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Trip\Models\Trip;
use Modules\Trip\Models\TripMember;

class TripInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Trip $trip,
        public readonly TripMember $member,
        public readonly string $verifyUrl,
        public readonly string $otp,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You're invited to join \"{$this->trip->name}\" on TripLedger",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'core::mail.trip-invite',
        );
    }
}
