<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
it('allows a user to login with valid credentials', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);

    $response = $this->postJson('/api/v1/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['token', 'user']);
});

it('rejects login with invalid credentials', function () {
    $user = User::factory()->create();

    $this->postJson('/api/v1/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertUnauthorized();
});

it('allows a logged in user to logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/v1/logout')
        ->assertOk();
});

it('returns the authenticated user on /me', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonFragment(['email' => $user->email]);
});
