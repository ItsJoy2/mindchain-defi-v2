<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $title;
    public $subtitle;
    public $messageText;

    public function __construct($otp, $title = null, $subtitle = null, $messageText = null)
    {
        $this->otp = $otp;

        $this->title = $title ?? 'OTP Verification';

        $this->subtitle = $subtitle ?? 'Secure verification process';

        $this->messageText = $messageText
            ?? 'Use the verification code below to continue securely.';
    }

    public function build()
    {
        return $this->subject($this->title)
                    ->view('emails.otpVerification');
    }
}
