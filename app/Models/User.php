<?php

namespace App\Models;

use App\Enums\Ability;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'is_active', 'must_change_password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /** @var Collection<int, string>|null Per-request cache of permission names. */
    private ?Collection $permissionNames = null;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
        ];
    }

    /** @return BelongsToMany<Role, $this> */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /** @param Builder<User> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function hasRole(string $role): bool
    {
        return $this->roles->contains('name', $role);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(Role::ADMIN);
    }

    /**
     * Whether any of the user's roles grants the permission.
     * Admins implicitly hold every permission.
     */
    public function hasPermission(Ability|string $ability): bool
    {
        $name = $ability instanceof Ability ? $ability->value : $ability;

        return $this->isAdmin() || $this->permissionNames()->contains($name);
    }

    /**
     * Permission check that also respects the abilities of the API token
     * used for the current request. Session (browser) requests have no
     * token restriction.
     */
    public function canUse(Ability|string $ability): bool
    {
        $name = $ability instanceof Ability ? $ability->value : $ability;

        if (! $this->hasPermission($name)) {
            return false;
        }

        $token = $this->currentAccessToken();

        return $token === null || $token->can($name);
    }

    /** @return Collection<int, string> */
    public function permissionNames(): Collection
    {
        if ($this->isAdmin()) {
            return collect(Ability::values());
        }

        return $this->permissionNames ??= $this->roles()
            ->with('permissions:id,name')
            ->get()
            ->flatMap(fn (Role $role) => $role->permissions->pluck('name'))
            ->unique()
            ->values();
    }

    /** Clears memoised roles/permissions after they change. */
    public function flushPermissionCache(): void
    {
        $this->permissionNames = null;
        $this->unsetRelation('roles');
    }
}
