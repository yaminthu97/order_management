<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EcOrderCsvSendMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $filePath;
    public $fileName;
    public $fromEmail;

    /**
     * Create a new message instance.
     */
    public function __construct($filePath, $fileName, $fromEmail)
    {
        $this->filePath = $filePath;
        $this->fileName =  $fileName;
        $this->fromEmail = $fromEmail;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '通信販売売上・受注残（CSV）',
        );
    }

    /**
     * Get the message content definition.
     */
    public function build()
    {
        $email = $this->from($this->fromEmail)->html('<p>通信販売売上・受注残（CSV）を添付します。</p>');

        $fileName = $this->fileName . '.csv';

        if (isset($this->filePath) && file_exists($this->filePath)) {
            $email->attach($this->filePath, [
                'as' => $fileName,
                'mime' => 'text/csv',
            ]);
        }

        return $email;
    }
}
