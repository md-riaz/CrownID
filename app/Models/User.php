<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;
use Laravel\Passport\HasApiTokens;
use PragmaRX\Google2FA\Google2FA;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'realm_id',
        'username',
        'name',
        'email',
        'password',
        'attributes',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'attributes' => 'array',
            'two_factor_enabled' => 'boolean',
            'account_locked_until' => 'datetime',
        ];
    }

    public function realm(): BelongsTo
    {
        return $this->belongsTo(Realm::class);
    }

    public function directRoles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'crownid_role_user');
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_user');
    }

    public function getAllRoles(): array
    {
        $this->load('directRoles', 'groups.roles', 'groups.parent');
        
        $roles = $this->directRoles->all();
        
        foreach ($this->groups as $group) {
            $groupRoles = $group->roles;
            if ($group->parent) {
                $parentRoleNames = $group->parent->getAllRoles();
                $parentRoleModels = Role::whereIn('name', $parentRoleNames)
                    ->where('realm_id', $this->realm_id)
                    ->get();
                $groupRoles = $groupRoles->merge($parentRoleModels);
            }
            $roles = array_merge($roles, $groupRoles->all());
        }
        
        $uniqueRoles = [];
        $seen = [];
        foreach ($roles as $role) {
            if (!in_array($role->id, $seen)) {
                $uniqueRoles[] = $role;
                $seen[] = $role->id;
            }
        }
        
        return $uniqueRoles;
    }

    public function requiredActions(): HasMany
    {
        return $this->hasMany(RequiredAction::class);
    }

    public function backupCodes(): HasMany
    {
        return $this->hasMany(BackupCode::class);
    }

    public function loginAttempts(): HasMany
    {
        return $this->hasMany(LoginAttempt::class);
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_enabled && $this->two_factor_secret !== null;
    }

    public function generateTwoFactorSecret(): string
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        $this->two_factor_secret = Crypt::encryptString($secret);
        $this->save();
        return $secret;
    }

    public function getTwoFactorSecret(): ?string
    {
        if ($this->two_factor_secret === null) {
            return null;
        }
        return Crypt::decryptString($this->two_factor_secret);
    }

    public function verifyTwoFactorCode(string $code): bool
    {
        $secret = $this->getTwoFactorSecret();
        if ($secret === null) {
            return false;
        }
        $google2fa = new Google2FA();
        return $google2fa->verifyKey($secret, $code);
    }

    public function lockAccount(int $minutes): void
    {
        $this->account_locked_until = now()->addMinutes($minutes);
        $this->save();
    }

    public function isAccountLocked(): bool
    {
        if ($this->account_locked_until === null) {
            return false;
        }
        if (now()->greaterThan($this->account_locked_until)) {
            $this->account_locked_until = null;
            $this->failed_login_attempts = 0;
            $this->save();
            return false;
        }
        return true;
    }

    public function incrementFailedLoginAttempts(): void
    {
        $this->increment('failed_login_attempts');
    }

    public function resetFailedLoginAttempts(): void
    {
        $this->failed_login_attempts = 0;
        $this->account_locked_until = null;
        $this->save();
    }
}
