<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Realm;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CrownIDSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $masterRealm = Realm::updateOrCreate(
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

        $testUser = User::updateOrCreate(
            [
                'realm_id' => $masterRealm->id,
                'username' => 'admin',
            ],
            [
                'name' => 'Admin User',
                'email' => 'admin@crownid.local',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $testClient = Client::firstOrNew([
            'realm_id' => $masterRealm->id,
            'client_id' => 'test-client',
        ]);

        if (!$testClient->exists) {
            $testClient->id = Str::uuid();
        }

        $testClient->fill([
            'name' => 'Test Client',
            'secret' => Hash::make('test-secret'),
            'redirect_uris' => json_encode(['http://localhost:8000/callback']),
            'grant_types' => json_encode(['authorization_code', 'refresh_token']),
            'client_type' => 'confidential',
            'enabled' => true,
            'revoked' => false,
        ])->save();

        $this->command->info('Created master realm');
        $this->command->info("Created test user: {$testUser->email} / password");
        $this->command->info("Created test client: {$testClient->client_id} / test-secret");
    }
}
