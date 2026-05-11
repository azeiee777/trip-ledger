<?php

namespace Modules\Core\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Expense\Models\Expense;
use Modules\Trip\Models\Trip;

class ExpensePendingApprovalMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Trip $trip,
        public readonly Expense $expense,
        public readonly string $approveUrl,
        public readonly string $rejectUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[Action Required] New expense pending approval in \"{$this->trip->name}\"",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'core::mail.expense-pending-approval',
        );
    }
}
