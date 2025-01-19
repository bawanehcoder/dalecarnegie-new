<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property bool $isPublic
 */
class Course extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const TYPE_PUBLIC = 'public';
    public const TYPE_CORPORATE = 'corporate';

    protected $fillable = [
        'type',
        'locations',
        'name',
        'details',
        'duration',
        'duration_type',
        'price',
        'invoiced',
        'company_id',
        'lead_id',
        'course_type',
        'start_date',
        'end_date',
    ];

    public function getLocationAttribute($value)
    {
        switch ($value) {
            case 1:
                return 'on site';
            case 0:
                return 'other site';
        }
    }

    public function getTypeAttribute($value)
    {
        switch ($value) {
            case 0:
                return self::TYPE_PUBLIC;
            case 1:
                return self::TYPE_CORPORATE;
        }

    }

    public function setTypeAttribute($value)
    {
        switch ($value) {
            case self::TYPE_PUBLIC:
                return $this->attributes['type'] = 0;
            case self::TYPE_CORPORATE:
                return $this->attributes['type'] = 1;
        }
        
    }

    public function getIsPublicAttribute(): bool
    {
        return $this->type == self::TYPE_PUBLIC;
    }

    public function sumTraineePayments()
    {
        return $this->traineePayments()->sum('amount');
    }


    public function getPaymentsAttribute()
    {
        return $this->traineePayments()->sum('amount');
    }

    public function traineePayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'course_id')
            ->whereIn('entity_type', ['company', 'trainee']);
    }

    public function getTraineePayments()
    {
        return $this->traineePayments()->get();
    }

    public function userCount(): int
    {
        return $this->hasMany(CoursesTrainee::class, 'course_id')->count();
    }

    public function getSchedule(): Collection
    {
        return $this->schedule()->get();
    }

    public function schedule(): HasMany
    {
        return $this->hasMany(Schedule::class, 'course_id');
    }

    public function getTrainers(): Collection
    {
        return $this->trainers()->get();
    }

    public function trainers(): HasMany
    {
        return $this->hasMany(CourseTrainer::class, 'course_id');
    }

    public function getTrainees(): Collection
    {
        return $this->trainees()->get();
    }

    public function trainees(): HasMany
    {
        return $this->hasMany(CoursesTrainee::class, 'course_id');
    }

    public function topics(): BelongsToMany
    {
        return $this->belongsToMany(Topic::class)->withTimestamps();
    }

    public function userPrice()
    {
        return $this->trainees()->sum('price');
    }

    public function getTotalPriceAttribute()
    {
        return $this->isPublic ? $this->userPrice() : $this->price;
    }

    public function totalPrice()
    {
        return $this->isPublic ? $this->userPrice() : $this->price;
    }

    public function getCompanyNameAttribute()
    {
        return $this->company->name ?? "";
    }

    


    public function getLeaderNameAttribute()
    {
        return $this->leader->name ?? "";
    }

    public function allTrainees(): BelongsToMany
    {
        return $this->belongsToMany(Trainee::class, 'courses_trainees', 'course_id', 'user_id');
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(Supervisor::class, 'lead_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function pays(){
        return $this->hasMany(Payment::class);
    }
}
