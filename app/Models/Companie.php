<?php

namespace App\Models;

use App\Traits\AvatarTrait;
use App\Traits\HasMediaTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;

class Companie extends Model implements  HasMedia
{
    use HasFactory,
        SoftDeletes,
        HasMediaTrait,
        AvatarTrait
        ;
    protected $CollectionName='companies';
    protected $fillable = ['name', 'email', 'phone', 'active', 'restricted_at','liaison_officer'];
    public function trainee(){
        return $this->hasMany(CompanyUser::class,'company_id');
    }
}
