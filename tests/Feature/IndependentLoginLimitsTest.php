<?php

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

it('limits web login attempts against one account across different IPs', function () {
    for ($attempt = 1; $attempt <= 5; $attempt++) {
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.'.$attempt])
            ->postJson('/login', ['email' => 'target@example.com', 'password' => 'wrong'])
            ->assertUnprocessable();
    }

    $writes = ActivityLog::where('action', 'auth.failed')->count();
    $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.99'])
        ->postJson('/login', ['email' => 'TARGET@example.com', 'password' => 'wrong'])
        ->assertStatus(429);
    expect(ActivityLog::where('action', 'auth.failed')->count())->toBe($writes);
});

it('limits web password spraying from one IP across different accounts', function () {
    for ($attempt = 1; $attempt <= 30; $attempt++) {
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.100'])
            ->postJson('/login', ['email' => 'target'.$attempt.'@example.com', 'password' => 'wrong'])
            ->assertUnprocessable();
    }

    $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.100'])
        ->postJson('/login', ['email' => 'another@example.com', 'password' => 'wrong'])
        ->assertStatus(429);
});

it('limits API login attempts against one account across different IPs', function () {
    for ($attempt = 1; $attempt <= 5; $attempt++) {
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.'.$attempt])
            ->postJson('/api/login', ['email' => 'target@example.com', 'password' => 'wrong'])
            ->assertUnauthorized();
    }

    $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.99'])
        ->postJson('/api/login', ['email' => 'TARGET@example.com', 'password' => 'wrong'])
        ->assertStatus(429);
});

it('limits API password spraying from one IP across different accounts', function () {
    for ($attempt = 1; $attempt <= 30; $attempt++) {
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.100'])
            ->postJson('/api/login', ['email' => 'target'.$attempt.'@example.com', 'password' => 'wrong'])
            ->assertUnauthorized();
    }

    $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.100'])
        ->postJson('/api/login', ['email' => 'another@example.com', 'password' => 'wrong'])
        ->assertStatus(429);
});
