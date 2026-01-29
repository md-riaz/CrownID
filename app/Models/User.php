<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
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
}
