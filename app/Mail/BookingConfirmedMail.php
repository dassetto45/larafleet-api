<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Prenotazione confermata — ' . $this->booking->vehicle->brand . ' ' . $this->booking->vehicle->model,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.booking.confirmed',
        );
    }
}
