<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Hash;

/**
 * @property int $id
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Trainee extends User
{
    protected $table = 'users';

    protected static function booted()
    {
        static::addGlobalScope('trainee', function ($model) {
            return $model->where('type', User::TRAINEE);
        });

        static::creating(function ($model) {
            $model->type = User::TRAINEE;
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

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'courses_trainees', 'user_id')
            ->withPivotValue('price', 0);
    }
}
