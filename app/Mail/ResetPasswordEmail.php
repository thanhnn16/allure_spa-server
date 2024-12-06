<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $resetUrl;
    public $token;
    public $lang;

    public function __construct($resetUrl, $token, $lang = 'vi')
    {
        $this->resetUrl = $resetUrl;
        $this->token = $token;
        $this->lang = $lang;
    }

    public function build()
    {
        return $this->view('emails.reset-password')
            ->subject(__('messages.reset_password_subject'));
    }
}
