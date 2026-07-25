<?php

namespace App\Mail;

use App\Models\Leave;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaveStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public Leave $leave;
    public string $status;

    public function __construct(Leave $leave, string $status)
    {
        $this->leave = $leave;
        $this->status = $status;
    }

    public function envelope(): Envelope
    {
        $subject = $this->status === 'approved'
            ? 'Leave Approved — ' . $this->leave->leaveType?->name
            : 'Leave Rejected — ' . $this->leave->leaveType?->name;

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.leave-status',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
