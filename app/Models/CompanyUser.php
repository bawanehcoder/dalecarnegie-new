<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $company_id
 * @property int $user_id
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class CompanyUser extends Model
{
    use HasFactory;

    protected $fillable = ['company_id','user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
