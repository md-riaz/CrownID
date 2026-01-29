<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $table = 'crownid_roles';
    
    protected $fillable = [
        'realm_id',
        'client_id',
        'name',
        'description',
        'composite',
    ];

    protected $casts = [
        'composite' => 'boolean',
    ];

    public function realm(): BelongsTo
    {
        return $this->belongsTo(Realm::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'crownid_role_user');
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'crownid_role_group');
    }

    public function childRoles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'crownid_composite_roles', 'parent_role_id', 'child_role_id');
    }

    public function parentRoles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'crownid_composite_roles', 'child_role_id', 'parent_role_id');
    }

    public function isRealmRole(): bool
    {
        return $this->client_id === null;
    }

    public function isClientRole(): bool
    {
        return $this->client_id !== null;
    }

    public function expandComposite(): array
    {
        $roles = [$this->name];
        
        if ($this->composite) {
            foreach ($this->childRoles as $childRole) {
                $roles = array_merge($roles, $childRole->expandComposite());
            }
        }
        
        return array_unique($roles);
    }
}
