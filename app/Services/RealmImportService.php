<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Group;
use App\Models\Realm;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class RealmImportService
{
    public function importRealm(array $realmData): Realm
    {
        $this->validateRealmData($realmData);

        return DB::transaction(function () use ($realmData) {
            $realm = $this->createOrUpdateRealm($realmData);
            
            $this->importClients($realm, $realmData['clients'] ?? []);
            $this->importRoles($realm, $realmData['roles'] ?? []);
            $this->importGroups($realm, $realmData['groups'] ?? []);
            
            if (isset($realmData['users']) && !empty($realmData['users'])) {
                $this->importUsers($realm, $realmData['users']);
            }
            
            $this->importCompositeRoles($realm, $realmData['roles'] ?? []);
            
            return $realm->fresh();
        });
    }

    protected function validateRealmData(array $realmData): void
    {
        if (empty($realmData['realm'])) {
            throw ValidationException::withMessages([
                'realm' => ['Realm name is required in import data'],
            ]);
        }
    }

    protected function createOrUpdateRealm(array $realmData): Realm
    {
        return Realm::updateOrCreate(
            ['name' => $realmData['realm']],
            [
                'display_name' => $realmData['displayName'] ?? $realmData['realm'],
                'enabled' => $realmData['enabled'] ?? true,
                'access_token_lifespan' => $realmData['accessTokenLifespan'] ?? 300,
                'refresh_token_lifespan' => $realmData['refreshTokenLifespan'] ?? 1800,
                'sso_session_idle_timeout' => $realmData['ssoSessionIdleTimeout'] ?? 1800,
                'sso_session_max_lifespan' => $realmData['ssoSessionMaxLifespan'] ?? 36000,
            ]
        );
    }

    protected function importClients(Realm $realm, array $clients): void
    {
        foreach ($clients as $clientData) {
            Client::updateOrCreate(
                [
                    'realm_id' => $realm->id,
                    'client_id' => $clientData['clientId'],
                ],
                [
                    'id' => $clientData['id'] ?? $clientData['clientId'],
                    'name' => $clientData['name'] ?? $clientData['clientId'],
                    'secret' => $clientData['secret'] ?? null,
                    'redirect_uris' => json_encode($clientData['redirectUris'] ?? []),
                    'grant_types' => ['authorization_code', 'refresh_token'],
                    'enabled' => $clientData['enabled'] ?? true,
                    'revoked' => false,
                ]
            );
        }
    }

    protected function importRoles(Realm $realm, array $rolesData): void
    {
        $realmRoles = $rolesData['realm'] ?? [];
        foreach ($realmRoles as $roleData) {
            Role::updateOrCreate(
                [
                    'realm_id' => $realm->id,
                    'client_id' => null,
                    'name' => $roleData['name'],
                ],
                [
                    'description' => $roleData['description'] ?? null,
                    'composite' => $roleData['composite'] ?? false,
                ]
            );
        }

        $clientRoles = $rolesData['client'] ?? [];
        foreach ($clientRoles as $clientId => $roles) {
            $client = Client::where('realm_id', $realm->id)
                ->where('client_id', $clientId)
                ->first();

            if (!$client) {
                continue;
            }

            foreach ($roles as $roleData) {
                Role::updateOrCreate(
                    [
                        'realm_id' => $realm->id,
                        'client_id' => $client->id,
                        'name' => $roleData['name'],
                    ],
                    [
                        'description' => $roleData['description'] ?? null,
                        'composite' => $roleData['composite'] ?? false,
                    ]
                );
            }
        }
    }

    protected function importCompositeRoles(Realm $realm, array $rolesData): void
    {
        $realmRoles = $rolesData['realm'] ?? [];
        foreach ($realmRoles as $roleData) {
            if (isset($roleData['composites']) && !empty($roleData['composites'])) {
                $parentRole = Role::where('realm_id', $realm->id)
                    ->whereNull('client_id')
                    ->where('name', $roleData['name'])
                    ->first();

                if ($parentRole) {
                    $this->attachCompositeRoles($realm, $parentRole, $roleData['composites']);
                }
            }
        }

        $clientRoles = $rolesData['client'] ?? [];
        foreach ($clientRoles as $clientId => $roles) {
            $client = Client::where('realm_id', $realm->id)
                ->where('client_id', $clientId)
                ->first();

            if (!$client) {
                continue;
            }

            foreach ($roles as $roleData) {
                if (isset($roleData['composites']) && !empty($roleData['composites'])) {
                    $parentRole = Role::where('realm_id', $realm->id)
                        ->where('client_id', $client->id)
                        ->where('name', $roleData['name'])
                        ->first();

                    if ($parentRole) {
                        $this->attachCompositeRoles($realm, $parentRole, $roleData['composites']);
                    }
                }
            }
        }
    }

    protected function attachCompositeRoles(Realm $realm, Role $parentRole, array $composites): void
    {
        $childRoleIds = [];

        foreach ($composites['realm'] ?? [] as $roleName) {
            $role = Role::where('realm_id', $realm->id)
                ->whereNull('client_id')
                ->where('name', $roleName)
                ->first();
            if ($role) {
                $childRoleIds[] = $role->id;
            }
        }

        foreach ($composites['client'] ?? [] as $clientId => $roleNames) {
            $client = Client::where('realm_id', $realm->id)
                ->where('client_id', $clientId)
                ->first();

            if ($client) {
                foreach ($roleNames as $roleName) {
                    $role = Role::where('realm_id', $realm->id)
                        ->where('client_id', $client->id)
                        ->where('name', $roleName)
                        ->first();
                    if ($role) {
                        $childRoleIds[] = $role->id;
                    }
                }
            }
        }

        if (!empty($childRoleIds)) {
            $parentRole->childRoles()->sync($childRoleIds);
        }
    }

    protected function importGroups(Realm $realm, array $groups, ?int $parentId = null): void
    {
        foreach ($groups as $groupData) {
            $group = Group::updateOrCreate(
                [
                    'realm_id' => $realm->id,
                    'path' => $groupData['path'],
                ],
                [
                    'name' => $groupData['name'],
                    'parent_id' => $parentId,
                ]
            );

            $this->attachGroupRoles($realm, $group, $groupData);

            if (isset($groupData['subGroups']) && !empty($groupData['subGroups'])) {
                $this->importGroups($realm, $groupData['subGroups'], $group->id);
            }
        }
    }

    protected function attachGroupRoles(Realm $realm, Group $group, array $groupData): void
    {
        $roleIds = [];

        foreach ($groupData['realmRoles'] ?? [] as $roleName) {
            $role = Role::where('realm_id', $realm->id)
                ->whereNull('client_id')
                ->where('name', $roleName)
                ->first();
            if ($role) {
                $roleIds[] = $role->id;
            }
        }

        foreach ($groupData['clientRoles'] ?? [] as $clientId => $roleNames) {
            $client = Client::where('realm_id', $realm->id)
                ->where('client_id', $clientId)
                ->first();

            if ($client) {
                foreach ($roleNames as $roleName) {
                    $role = Role::where('realm_id', $realm->id)
                        ->where('client_id', $client->id)
                        ->where('name', $roleName)
                        ->first();
                    if ($role) {
                        $roleIds[] = $role->id;
                    }
                }
            }
        }

        if (!empty($roleIds)) {
            $group->roles()->sync($roleIds);
        }
    }

    protected function importUsers(Realm $realm, array $users): void
    {
        foreach ($users as $userData) {
            $password = null;
            if (isset($userData['credentials']) && !empty($userData['credentials'])) {
                foreach ($userData['credentials'] as $credential) {
                    if ($credential['type'] === 'password') {
                        $password = $credential['hashedSaltedValue'] ?? null;
                        if ($password && !str_starts_with($password, '$2y$')) {
                            $password = Hash::make($password);
                        }
                        break;
                    }
                }
            }

            $user = User::updateOrCreate(
                [
                    'realm_id' => $realm->id,
                    'username' => $userData['username'],
                ],
                [
                    'name' => $userData['username'],
                    'email' => $userData['email'] ?? null,
                    'password' => $password ?? Hash::make('password'),
                    'email_verified_at' => ($userData['emailVerified'] ?? false) ? now() : null,
                ]
            );

            $this->attachUserRoles($realm, $user, $userData);
            $this->attachUserGroups($realm, $user, $userData);
        }
    }

    protected function attachUserRoles(Realm $realm, User $user, array $userData): void
    {
        $roleIds = [];

        foreach ($userData['realmRoles'] ?? [] as $roleName) {
            $role = Role::where('realm_id', $realm->id)
                ->whereNull('client_id')
                ->where('name', $roleName)
                ->first();
            if ($role) {
                $roleIds[] = $role->id;
            }
        }

        foreach ($userData['clientRoles'] ?? [] as $clientId => $roleNames) {
            $client = Client::where('realm_id', $realm->id)
                ->where('client_id', $clientId)
                ->first();

            if ($client) {
                foreach ($roleNames as $roleName) {
                    $role = Role::where('realm_id', $realm->id)
                        ->where('client_id', $client->id)
                        ->where('name', $roleName)
                        ->first();
                    if ($role) {
                        $roleIds[] = $role->id;
                    }
                }
            }
        }

        if (!empty($roleIds)) {
            $user->directRoles()->sync($roleIds);
        }
    }

    protected function attachUserGroups(Realm $realm, User $user, array $userData): void
    {
        $groupIds = [];

        foreach ($userData['groups'] ?? [] as $groupPath) {
            $group = Group::where('realm_id', $realm->id)
                ->where('path', $groupPath)
                ->first();
            if ($group) {
                $groupIds[] = $group->id;
            }
        }

        if (!empty($groupIds)) {
            $user->groups()->sync($groupIds);
        }
    }
}
