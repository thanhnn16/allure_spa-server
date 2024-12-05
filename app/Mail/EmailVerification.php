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
        $verificationUrl = config('app.url') . '/api/email/verify/' . $this->token->token;

        return $this->view('emails.verify-email')
            ->with([
                'verificationUrl' => $verificationUrl,
                'token' => $this->token->token
            ])
            ->subject(__('Email Verification'));
    }
}
