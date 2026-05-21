<?php

use App\Models\Booking;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
it('allows a user to book an available vehicle', function () {
    Queue::fake();

    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;
    $vehicle = Vehicle::factory()->create(['status' => 'available']);

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/v1/bookings', [
            'vehicle_id' => $vehicle->id,
            'start_at' => now()->addDay()->toDateTimeString(),
            'end_at' => now()->addDays(3)->toDateTimeString(),
        ]);

    $response->assertCreated();

    $this->assertDatabaseHas('bookings', ['vehicle_id' => $vehicle->id]);
    $vehicle->refresh();
    expect($vehicle->status)->toBe('in_use');
});

it('rejects booking an unavailable vehicle', function () {
    $user = User::factory()->create();
    $vehicle = Vehicle::factory()->create(['status' => 'in_use']);
    $token = $user->createToken('test')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer ' . $token)->postJson('/api/v1/bookings', [
        'vehicle_id' => $vehicle->id,
        'start_at' => now()->addDay()->toDateTimeString(),
        'end_at' => now()->addDays(3)->toDateTimeString(),
    ])->assertUnprocessable();
});

it('prevents a user from cancelling another user booking', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $vehicle = Vehicle::factory()->create();
    $booking = Booking::factory()->create(['user_id' => $other->id, 'vehicle_id' => $vehicle->id]);

    $this->actingAs($user)->deleteJson("/api/v1/bookings/{$booking->id}")
        ->assertForbidden();
});

it('it dispatches a confirmation job when booking is created', function () {
    Queue::fake();

    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;
    $vehicle = Vehicle::factory()->create(['status' => 'available']);

    $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/v1/bookings', [
            'vehicle_id' => $vehicle->id,
            'start_at' => now()->addDay()->toDateTimeString(),
            'end_at' => now()->addDays(3)->toDateTimeString(),
        ]);

    Queue::assertPushed(\App\Jobs\SendBookingConfirmationJob::class);
});
