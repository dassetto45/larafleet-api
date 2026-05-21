<?php

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
it('returns a list of vehicles', function () {
    Vehicle::factory(3)->create();
    $user = User::factory()->create();

    $this->actingAs($user)->getJson('/api/v1/vehicles')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('allows an admin to create a vehicle', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->postJson('/api/v1/vehicles', [
        'plate' => 'AB123CD',
        'brand' => 'Fiat',
        'model' => 'Panda',
        'year' => 2022,
        'type' => 'car',
    ])->assertCreated();

    $this->assertDatabaseHas('vehicles', ['plate' => 'AB123CD']);
});

it('prevents a regular user from creating a vehicle', function () {
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)->postJson('/api/v1/vehicles', [
        'plate' => 'AB123CD',
        'brand' => 'Fiat',
        'model' => 'Panda',
        'year' => 2022,
        'type' => 'car',
    ])->assertForbidden();
});

it('filters vehicles by status', function () {
    Vehicle::factory(2)->create(['status' => 'available']);
    Vehicle::factory(1)->create(['status' => 'in_use']);
    $user = User::factory()->create();

    $this->actingAs($user)->getJson('/api/v1/vehicles?status=available')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});
