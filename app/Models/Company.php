<?php

namespace App\Models;

use App\Traits\AvatarTrait;
use App\Traits\HasMediaTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;

class Company extends Model implements HasMedia
{
    use HasFactory;
    use SoftDeletes;
    use HasMediaTrait;
    use AvatarTrait;

    protected string $collectionName = 'companies';
    protected $casts = [
        'liaison_officer' => 'array',
    ];

    protected $fillable = ['name', 'email', 'phone', 'active', 'note', 'restricted_at','liaison_officer'];

    public function trainee(): HasMany
    {
        return $this->hasMany(CompanyUser::class, 'company_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_users');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'company', 'entity_type', 'entity_id');
    }
}
