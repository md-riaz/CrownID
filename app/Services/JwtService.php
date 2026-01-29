<?php

namespace App\Services;

use App\Models\Realm;
use App\Models\User;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token\Plain;

class JwtService
{
    protected Configuration $config;

    public function __construct()
    {
        $privateKey = $this->getPrivateKey();
        $publicKey = $this->getPublicKey();
        
        $this->config = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::plainText($privateKey),
            InMemory::plainText($publicKey)
        );
    }

    public function createIdToken(User $user, Realm $realm, string $clientId, ?string $nonce = null): string
    {
        $now = new \DateTimeImmutable();
        $issuer = $this->getIssuer($realm);
        
        $builder = $this->config->builder()
            ->issuedBy($issuer)
            ->permittedFor($clientId)
            ->identifiedBy(bin2hex(random_bytes(16)))
            ->issuedAt($now)
            ->expiresAt($now->modify('+1 hour'))
            ->relatedTo((string) $user->id)
            ->withClaim('preferred_username', $user->username ?? $user->email)
            ->withClaim('email', $user->email)
            ->withClaim('email_verified', $user->email_verified_at !== null)
            ->withHeader('kid', $this->getKid());

        if ($nonce) {
            $builder->withClaim('nonce', $nonce);
        }

        if ($user->name) {
            $builder->withClaim('name', $user->name);
        }

        $roleClaims = $this->buildRoleClaims($user, $clientId);
        if (!empty($roleClaims['realm_access'])) {
            $builder = $builder->withClaim('realm_access', $roleClaims['realm_access']);
        }
        if (!empty($roleClaims['resource_access'])) {
            $builder = $builder->withClaim('resource_access', $roleClaims['resource_access']);
        }

        return $builder->getToken($this->config->signer(), $this->config->signingKey())->toString();
    }

    public function createAccessToken(User $user, Realm $realm, string $clientId, array $scopes = []): string
    {
        $now = new \DateTimeImmutable();
        $issuer = $this->getIssuer($realm);
        $expiresIn = $realm->access_token_lifespan ?? 300;
        
        $builder = $this->config->builder()
            ->issuedBy($issuer)
            ->permittedFor($clientId)
            ->identifiedBy(bin2hex(random_bytes(16)))
            ->issuedAt($now)
            ->expiresAt($now->modify("+{$expiresIn} seconds"))
            ->relatedTo((string) $user->id)
            ->withClaim('typ', 'Bearer')
            ->withClaim('preferred_username', $user->username ?? $user->email)
            ->withClaim('email', $user->email)
            ->withClaim('scope', implode(' ', $scopes))
            ->withHeader('kid', $this->getKid())
            ->withHeader('typ', 'JWT');

        $roleClaims = $this->buildRoleClaims($user, $clientId);
        if (!empty($roleClaims['realm_access'])) {
            $builder = $builder->withClaim('realm_access', $roleClaims['realm_access']);
        }
        if (!empty($roleClaims['resource_access'])) {
            $builder = $builder->withClaim('resource_access', $roleClaims['resource_access']);
        }

        return $builder->getToken($this->config->signer(), $this->config->signingKey())->toString();
    }

    public function verifyToken(string $token): ?Plain
    {
        try {
            $parsed = $this->config->parser()->parse($token);
            
            if (!$parsed instanceof Plain) {
                return null;
            }

            $constraints = $this->config->validationConstraints();
            
            if (!$this->config->validator()->validate($parsed, ...$constraints)) {
                return null;
            }

            return $parsed;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getJwks(): array
    {
        $publicKey = $this->getPublicKey();
        
        $key = openssl_pkey_get_public($publicKey);
        $details = openssl_pkey_get_details($key);
        
        return [
            'keys' => [
                [
                    'kid' => $this->getKid(),
                    'kty' => 'RSA',
                    'alg' => 'RS256',
                    'use' => 'sig',
                    'n' => rtrim(str_replace(['+', '/'], ['-', '_'], base64_encode($details['rsa']['n'])), '='),
                    'e' => rtrim(str_replace(['+', '/'], ['-', '_'], base64_encode($details['rsa']['e'])), '='),
                ]
            ]
        ];
    }

    protected function getPrivateKey(): string
    {
        $keyPath = storage_path('oauth-private.key');
        
        if (!file_exists($keyPath)) {
            throw new \RuntimeException('OAuth private key not found. Run php artisan passport:keys');
        }
        
        return file_get_contents($keyPath);
    }

    protected function getPublicKey(): string
    {
        $keyPath = storage_path('oauth-public.key');
        
        if (!file_exists($keyPath)) {
            throw new \RuntimeException('OAuth public key not found. Run php artisan passport:keys');
        }
        
        return file_get_contents($keyPath);
    }

    protected function getKid(): string
    {
        return 'default-kid-' . md5($this->getPublicKey());
    }

    protected function getIssuer(Realm $realm): string
    {
        return config('app.url') . '/realms/' . $realm->name;
    }

    protected function buildRoleClaims(User $user, string $clientId): array
    {
        $allRoles = $user->getAllRoles();
        
        $realmRoles = [];
        $clientRolesMap = [];
        
        foreach ($allRoles as $role) {
            $expandedRoles = $role->expandComposite();
            
            if ($role->isRealmRole()) {
                $realmRoles = array_merge($realmRoles, $expandedRoles);
            } else {
                $client = $role->client;
                if ($client) {
                    if (!isset($clientRolesMap[$client->id])) {
                        $clientRolesMap[$client->id] = [];
                    }
                    $clientRolesMap[$client->id] = array_merge(
                        $clientRolesMap[$client->id],
                        $expandedRoles
                    );
                }
            }
        }
        
        $realmRoles = array_unique($realmRoles);
        
        $result = [];
        
        if (!empty($realmRoles)) {
            $result['realm_access'] = [
                'roles' => array_values($realmRoles)
            ];
        }
        
        if (!empty($clientRolesMap)) {
            $result['resource_access'] = [];
            foreach ($clientRolesMap as $cid => $roles) {
                $client = \App\Models\Client::find($cid);
                if ($client) {
                    $result['resource_access'][$client->client_id] = [
                        'roles' => array_values(array_unique($roles))
                    ];
                }
            }
        }
        
        return $result;
    }
}
