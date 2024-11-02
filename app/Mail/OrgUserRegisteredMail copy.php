<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrgUserRegisteredMail extends Mailable
{
    use Queueable, SerializesModels;

    public $org;

    
    public function __construct($org)
    {
        $this->org = $org;
    }
    
    public function build()
    {
        return $this->subject('Welcome to Our Application (org)')
                    ->view('emails.org.org_user_registered')
                    ->with(['org' => $this->org]);
    }
}
