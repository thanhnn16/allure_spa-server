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
    public $lang;
    public function __construct(VerificationToken $token, string $lang = 'vi')
    {
        $this->token = $token;
        $this->lang = $lang;
    }

    public function build()
    {
        $verificationUrl = config('app.url') . '/email/verify/' . $this->token->token . '?lang=' . $this->lang;

        return $this->view('emails.verify-email')
            ->with([
                'verificationUrl' => $verificationUrl,
                'token' => $this->token->token
            ])
            ->subject(__('messages.email_verification'));
    }
}
