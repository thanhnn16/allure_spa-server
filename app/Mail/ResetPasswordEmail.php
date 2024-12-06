<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $resetUrl;
    public $lang;

    public function __construct($resetUrl, $lang = 'vi')
    {
        $this->resetUrl = $resetUrl;
        $this->lang = $lang;
    }

    public function build()
    {
        return $this->subject(__('messages.reset_password'))
                    ->locale($this->lang)
                    ->view('emails.reset-password')
                    ->with([
                        'resetUrl' => $this->resetUrl
                    ]);
    }
}