<?php

namespace App\Services;

use App\Models\Realm;

class RealmExportService
{
    public function exportRealm(Realm $realm, bool $includeUsers = false): array
    {
        $realm->load([
            'clients',
            'roles',
            'groups.roles',
            'groups.parent',
        ]);

        if ($includeUsers) {
            $realm->load('users.directRoles', 'users.groups');
        }

        return [
            'realm' => $realm->name,
            'displayName' => $realm->display_name,
            'enabled' => $realm->enabled,
            'accessTokenLifespan' => $realm->access_token_lifespan,
            'refreshTokenLifespan' => $realm->refresh_token_lifespan,
            'ssoSessionIdleTimeout' => $realm->sso_session_idle_timeout,
            'ssoSessionMaxLifespan' => $realm->sso_session_max_lifespan,
            'clients' => $this->exportClients($realm),
            'roles' => $this->exportRoles($realm),
            'groups' => $this->exportGroups($realm),
            'users' => $includeUsers ? $this->exportUsers($realm) : [],
        ];
    }

    protected function exportClients(Realm $realm): array
    {
        return $realm->clients->map(function ($client) {
            return [
                'id' => $client->id,
                'clientId' => $client->client_id,
                'name' => $client->name,
                'secret' => $client->secret,
                'redirectUris' => $client->redirect_uris,
                'enabled' => $client->enabled,
                'clientAuthenticatorType' => 'client-secret',
                'protocol' => 'openid-connect',
            ];
        })->values()->all();
    }

    protected function exportRoles(Realm $realm): array
    {
        $realmRoles = $realm->roles()
            ->whereNull('client_id')
            ->with('childRoles')
            ->get();

        $clientRoles = [];
        foreach ($realm->clients as $client) {
            $roles = $client->roles()->with('childRoles')->get();
            if ($roles->isNotEmpty()) {
                $clientRoles[$client->client_id] = $roles->map(function ($role) {
                    return $this->mapRole($role);
                })->values()->all();
            }
        }

        return [
            'realm' => $realmRoles->map(function ($role) {
                return $this->mapRole($role);
            })->values()->all(),
            'client' => $clientRoles,
        ];
    }

    protected function mapRole($role): array
    {
        $mapped = [
            'id' => (string) $role->id,
            'name' => $role->name,
            'description' => $role->description,
            'composite' => $role->composite,
        ];

        if ($role->composite && $role->childRoles->isNotEmpty()) {
            $composites = [
                'realm' => [],
                'client' => [],
            ];

            foreach ($role->childRoles as $child) {
                if ($child->isRealmRole()) {
                    $composites['realm'][] = $child->name;
                } else {
                    $clientId = $child->client->client_id;
                    if (!isset($composites['client'][$clientId])) {
                        $composites['client'][$clientId] = [];
                    }
                    $composites['client'][$clientId][] = $child->name;
                }
            }

            $mapped['composites'] = $composites;
        }

        return $mapped;
    }

    protected function exportGroups(Realm $realm): array
    {
        $groups = $realm->groups()
            ->whereNull('parent_id')
            ->with(['children', 'roles'])
            ->get();

        return $groups->map(function ($group) {
            return $this->mapGroup($group);
        })->values()->all();
    }

    protected function mapGroup($group): array
    {
        $mapped = [
            'id' => (string) $group->id,
            'name' => $group->name,
            'path' => $group->path,
            'realmRoles' => $group->roles->filter(fn($r) => $r->isRealmRole())->pluck('name')->values()->all(),
            'clientRoles' => [],
        ];

        $clientRoles = $group->roles->filter(fn($r) => $r->isClientRole());
        foreach ($clientRoles as $role) {
            $clientId = $role->client->client_id;
            if (!isset($mapped['clientRoles'][$clientId])) {
                $mapped['clientRoles'][$clientId] = [];
            }
            $mapped['clientRoles'][$clientId][] = $role->name;
        }

        if ($group->children->isNotEmpty()) {
            $mapped['subGroups'] = $group->children->map(function ($child) {
                return $this->mapGroup($child);
            })->values()->all();
        }

        return $mapped;
    }

    protected function exportUsers(Realm $realm): array
    {
        return $realm->users->map(function ($user) {
            $realmRoles = $user->directRoles->filter(fn($r) => $r->isRealmRole())->pluck('name')->values()->all();
            $clientRoles = [];

            foreach ($user->directRoles->filter(fn($r) => $r->isClientRole()) as $role) {
                $clientId = $role->client->client_id;
                if (!isset($clientRoles[$clientId])) {
                    $clientRoles[$clientId] = [];
                }
                $clientRoles[$clientId][] = $role->name;
            }

            return [
                'id' => (string) $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'emailVerified' => $user->email_verified_at !== null,
                'enabled' => true,
                'credentials' => [
                    [
                        'type' => 'password',
                        'hashedSaltedValue' => $user->password,
                        'algorithm' => 'bcrypt',
                    ],
                ],
                'realmRoles' => $realmRoles,
                'clientRoles' => $clientRoles,
                'groups' => $user->groups->pluck('path')->values()->all(),
            ];
        })->values()->all();
    }
}
