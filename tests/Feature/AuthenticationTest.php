<?php

use App\Models\User;

use function Pest\Livewire\livewire;


it('allows a user to log in with valid credentials', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);

    livewire(\Filament\Auth\Pages\Login::class)
        ->fillForm([
            'email' => $user->email,
            'password' => 'password',
        ])
        ->call('authenticate');

    $this->assertAuthenticatedAs($user);
});

it('rejects invalid credentials', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password'),
    ]);

    livewire(\Filament\Auth\Pages\Login::class)
        ->fillForm([
            'email' => $user->email,
            'password' => 'wrong-password',
        ])
        ->call('authenticate');

    $this->assertGuest();
});

it('stores a database session record tied to the logged-in user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin');

    expect(\DB::table('sessions')->where('user_id', $user->id)->exists())->toBeTrue();
});

it('prevents guests from accessing gate in and gate out pages', function () {
    $this->get('/admin/gate-in')->assertRedirect('/admin/login');
    $this->get('/admin/gate-out')->assertRedirect('/admin/login');
});
