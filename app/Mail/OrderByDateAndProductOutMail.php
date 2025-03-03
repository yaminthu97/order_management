<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderByDateAndProductOutMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $fromEmail; // Define $fromEmail as a public property
    public $mailSubject; // Define $mailSubject as a public property
    public $mailContent; // Define $mailContent as a public property
    public $filePathArr; // Define $filePathArr as a public property

    /**
     * Create a new message instance.
     */
    public function __construct($fromEmail, $mailSubject, $mailContent, $filePathArr)
    {
        $this->fromEmail = $fromEmail; // Initialize $fromEmail in the constructor
        $this->mailSubject = $mailSubject; // Initialize $mailSubject in the constructor
        $this->mailContent = $mailContent; // Initialize $mailContent in the constructor
        $this->filePathArr = $filePathArr; // Initialize $filePathArr in the constructor
    }

    public function build()
    {

        $email = $this->from($this->fromEmail) // Use $fromEmail for the "from" address
            ->subject($this->mailSubject) // Set the email subject
            ->view('emails.order_date_product_out_mail') // Set the view template
            ->with([
                'content' => $this->mailContent, // Pass $mailContent to the view
            ]);

        // Check file path exist or not
        if (isset($this->filePathArr)) {
            foreach ($this->filePathArr as $filePath) {
                // Check file path exist or not
                if (file_exists($filePath)) {
                    $email->attach($filePath, [
                        'as' => basename($filePath), // Specify filename
                        'mime' => 'text/csv', // Mime type
                    ]);
                }
            }

        }
        return $email;

    }
}
