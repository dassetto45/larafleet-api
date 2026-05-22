<?php

namespace App\Jobs;

use App\Mail\BookingConfirmedMail;
use App\Models\Booking;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendBookingConfirmationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public Booking $booking)
    {
    }

    public function handle(): void
    {
        $this->booking->load(['user', 'vehicle']);

        Mail::to($this->booking->user->email)
            ->send(new BookingConfirmedMail($this->booking));
    }
}
