<?php

use App\Jobs\SendBookingConfirmationJob;
use App\Mail\BookingConfirmedMail;
use App\Models\Booking;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('sends a confirmation email when job is handled', function () {
    Mail::fake();

    $user = User::factory()->create();
    $vehicle = Vehicle::factory()->create();
    $booking = Booking::factory()->create([
        'user_id' => $user->id,
        'vehicle_id' => $vehicle->id,
    ]);

    (new SendBookingConfirmationJob($booking))->handle();

    Mail::assertSent(BookingConfirmedMail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});
