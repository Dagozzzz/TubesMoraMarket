<?php

namespace App\Mail;

use App\Models\Gaji;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GajiDibayarMail extends Mailable
{
    use Queueable, SerializesModels;

    public $gaji;

    public function __construct(Gaji $gaji)
    {
        $this->gaji = $gaji;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pemberitahuan Pembayaran Gaji - ' . $this->gaji->karyawan->nama,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.gaji_dibayar',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
