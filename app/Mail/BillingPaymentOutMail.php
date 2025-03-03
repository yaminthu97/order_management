<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BillingPaymentOutMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $fromEmail;
    public $subject;
    public $mailContent;

    public function __construct($fromEmail, $subject, $mailContent)
    {
        $this->fromEmail = $fromEmail;
        $this->subject = $subject;
        $this->mailContent = $mailContent;
    }

    public function build()
    {
        return $this
            ->from($this->fromEmail)
            ->subject($this->subject)
            ->view('emails.billing_payment_out_mail', ['mailContent' => $this->mailContent]);
    }
}
