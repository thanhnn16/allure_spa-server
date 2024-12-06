<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailVerificationSuccess extends Mailable
{
    use Queueable, SerializesModels;

    public $lang;

    public function __construct($lang = 'vi')
    {
        $this->lang = $lang;
    }

    public function build()
    {
        return $this->view('emails.verify-email-success')
            ->subject(__('messages.verification_successful'))
            ->with(['lang' => $this->lang]);
    }
} 