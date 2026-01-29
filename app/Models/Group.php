<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    protected $fillable = [
        'realm_id',
        'parent_id',
        'name',
        'path',
    ];

    public function realm(): BelongsTo
    {
        return $this->belongsTo(Realm::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Group::class, 'parent_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_user');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'crownid_role_group');
    }

    public function getAllRoles(): array
    {
        $roles = $this->roles->pluck('name')->toArray();
        
        if ($this->parent) {
            $roles = array_merge($roles, $this->parent->getAllRoles());
        }
        
        return array_unique($roles);
    }
}
