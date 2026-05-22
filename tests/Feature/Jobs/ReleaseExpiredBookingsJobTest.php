<?php

use App\Jobs\ReleaseExpiredBookingsJob;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('releases vehicles with expired bookings', function () {
    $vehicle = Vehicle::factory()->create(['status' => 'in_use']);
    $booking = Booking::factory()->create([
        'vehicle_id' => $vehicle->id,
        'status' => 'active',
        'start_at' => now()->subDays(3),
        'end_at' => now()->subDay(),
    ]);

    (new ReleaseExpiredBookingsJob)->handle();

    expect($booking->fresh()->status)->toBe('completed');
    expect($vehicle->fresh()->status)->toBe('available');
});

it('does not release bookings that are still active', function () {
    $vehicle = Vehicle::factory()->create(['status' => 'in_use']);
    $booking = Booking::factory()->create([
        'vehicle_id' => $vehicle->id,
        'status' => 'active',
        'start_at' => now()->subDay(),
        'end_at' => now()->addDays(2),
    ]);

    (new ReleaseExpiredBookingsJob)->handle();

    expect($booking->fresh()->status)->toBe('active');
    expect($vehicle->fresh()->status)->toBe('in_use');
});
