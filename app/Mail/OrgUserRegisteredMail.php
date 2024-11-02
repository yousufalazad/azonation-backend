<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class OrgUserRegisteredMail extends Mailable
{
    use Queueable, SerializesModels;

    public $org;
    public $verification_link;

    // public function __construct($org)
    // {
    //     $this->org = $org;

    //     // Generate a unique verification link
    //     $this->verification_link = url('/verify-account/' . Str::uuid()); // or Str::random(40)
    // }

    public function __construct($org)
    {
        $this->org = $org;

        // Generate a unique verification token
        $verification_token = Str::uuid();
        $this->verification_link = url('/api/verify-account/' . $verification_token);

        // Save the token to the user record for verification
        $this->org->update(['verification_token' => $verification_token]);
    }


    public function build()
    {
        return $this->subject('Welcome to Our Application!')
                    ->view('emails.org.org_user_registered')
                    ->with([
                        'org' => $this->org,
                        'verification_link' => $this->verification_link
                    ]);
    }
}