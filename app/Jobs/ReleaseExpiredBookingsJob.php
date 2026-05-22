<?php

namespace App\Jobs;

use App\Models\Booking;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ReleaseExpiredBookingsJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        Booking::query()
            ->where('status', 'active')
            ->where('end_at', '<', now())
            ->with('vehicle')
            ->each(function (Booking $booking) {
                $booking->update(['status' => 'completed']);
                $booking->vehicle->update(['status' => 'available']);
            });
    }
}
