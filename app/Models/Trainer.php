<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Hash;

class Trainer extends User
{
    protected $table = 'users';

    protected static function booted()
    {
        static::addGlobalScope('trainee', function ($model) {
            return $model->where('type', User::TRAINER);
        });

        static::creating(function ($model) {
            $model->type = User::TRAINER;
            $model->email = $model->email ?? fake()->unique()->safeEmail();
            $model->password = $model->password ?? Hash::make('password');
        });
    }

    public function setEmailAttribute($value): void
    {
        if (trim($value) == null) {
            $this->attributes['email'] = fake()->unique()->safeEmail();
            return;
        }

        $this->attributes['email'] = $value;
    }

    public function setPasswordAttribute($value): void
    {
        if (trim($value) == null) {
            $this->attributes['password'] = Hash::make('password');
            return;
        }

        $this->attributes['password'] = $value;
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'trainer', 'entity_type', 'entity_id');
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_trainers', 'user_id');
    }
}
