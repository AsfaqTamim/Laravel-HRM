<?php

namespace App\Mail;

use App\Models\Payslip;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PayslipGeneratedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Payslip $payslip;

    public function __construct(Payslip $payslip)
    {
        $this->payslip = $payslip;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payslip Generated — ' . $this->payslip->period,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.payslip-generated',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
