<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;


class AddMemberSuccessMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $individualData;
    public $orgName;

    /**
     * Create a new message instance.
     */
    public function __construct($individualData, $orgName)
    {
        $this->individualData = $individualData;
        $this->orgName = $orgName;
    }

    

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Add Member Success Email',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.org.add_member_success',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
