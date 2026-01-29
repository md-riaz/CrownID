<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Realm;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OidcTestSeeder extends Seeder
{
    public function run(): void
    {
        $realm = Realm::updateOrCreate(
            ['name' => 'master'],
            [
                'display_name' => 'Master',
                'enabled' => true,
                'access_token_lifespan' => 300,
                'refresh_token_lifespan' => 1800,
                'sso_session_idle_timeout' => 1800,
                'sso_session_max_lifespan' => 36000,
            ]
        );

        $user = User::updateOrCreate(
            [
                'realm_id' => $realm->id,
                'email' => 'admin@crownid.local'
            ],
            [
                'username' => 'admin',
                'name' => 'Admin User',
                'password' => Hash::make('admin'),
                'email_verified_at' => now(),
            ]
        );

        $client = Client::updateOrCreate(
            ['id' => 'test-client'],
            [
                'realm_id' => $realm->id,
                'client_id' => 'test-client',
                'name' => 'Test Client',
                'secret' => 'test-secret',
                'redirect_uris' => json_encode([
                    'http://localhost:3000/callback',
                    'http://localhost:8080/callback',
                ]),
                'grant_types' => json_encode(['authorization_code', 'refresh_token']),
                'client_type' => 'confidential',
                'enabled' => true,
                'revoked' => false,
            ]
        );

        $this->command->info('OIDC test data seeded successfully!');
        $this->command->info('Realm: ' . $realm->name);
        $this->command->info('User: ' . $user->email . ' / Password: admin');
        $this->command->info('Client ID: ' . $client->id . ' / Secret: test-secret');
    }
}
