<?php

namespace App\Models;

use App\Traits\AvatarTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\MediaLibrary\HasMedia;
use App\Traits\HasMediaTrait;

class User extends Authenticatable implements HasMedia
{
    // use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use HasRoles;
    use HasMediaTrait;
    use AvatarTrait;

    /** based on `type` column values */
    public const TRAINEE = 0; // standard user
    public const SUPER_ADMIN = 1;
    public const ADMIN = 2;
    public const TRAINER = 3;

    protected string $collectionName = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
        'active',
        'phone',
        'gender',
        'notes',
        'birth_date',
        'fees',
        'email_verified_at',
        'ex_company_name',
        'title',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'birth_date' => 'date',
        'password' => 'hashed',
    ];

    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['admin', 'Super-Admin']);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('Super-Admin');
    }

    public function topics(): BelongsToMany
    {
        return $this->belongsToMany(Topic::class, 'topic_user', 'user_id')->withTimestamps();
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_users', 'user_id');
    }
}
