<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SaleDataMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $filePath;
    public $fileName;
    public $fromMail;

    /**
     * Create a new message instance.
    */
    public function __construct($filePath, $fileName, $fromMail) {
        $this->filePath = $filePath;
        $this->fileName = $fileName;
        $this->fromMail = $fromMail;
    }

    /**
     * Get the message envelop.
    */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '商品別売上データ',
        );
    }

    public function build()
    {
        $email = $this->html('<p>店舗別・商品別の売上データです。</p>');

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