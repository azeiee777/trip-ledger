<?php

namespace Modules\Core\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Expense\Models\Expense;
use Modules\Trip\Models\Trip;

class ExpenseDecisionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Trip $trip,
        public readonly Expense $expense,
        public readonly string $decision,   // 'approved' | 'rejected'
        public readonly ?string $reason = null,
    ) {}

    public function envelope(): Envelope
    {
        $verb = $this->decision === 'approved' ? 'Approved ✅' : 'Rejected ❌';
        return new Envelope(
            subject: "Expense \"{$this->expense->title}\" {$verb} in \"{$this->trip->name}\"",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'core::mail.expense-decision',
        );
    }
}
