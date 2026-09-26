<?php

namespace App\Mail;

use App\Models\SellerVerification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WarungApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public SellerVerification $verification,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Warungmu terverifikasi — siap jualan di Warung Hebat!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.warung-approved',
            with: [
                'user' => $this->user,
                'verification' => $this->verification,
                'storeName' => $this->user->store?->name ?? 'Warungmu',
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
