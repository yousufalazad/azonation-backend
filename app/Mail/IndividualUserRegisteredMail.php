<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class IndividualUserRegisteredMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;


    public function __construct($user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('Welcome to Our Application')
            ->view('emails.individual.individual_user_registered')
            ->with(['user' => $this->user]);
    }
}
