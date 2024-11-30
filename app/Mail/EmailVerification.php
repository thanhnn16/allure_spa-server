<?php

namespace App\Mail;

use App\Models\VerificationToken;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailVerification extends Mailable
{
    use Queueable, SerializesModels;

    public $token;

    public function __construct(VerificationToken $token)
    {
        $this->token = $token;
    }

    public function build()
    {
        return $this->view('emails.verify-email')
            ->subject(__('Email Verification'));
    }
}
