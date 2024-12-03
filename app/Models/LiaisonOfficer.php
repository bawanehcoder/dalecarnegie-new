<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiaisonOfficer extends Model
{
    use HasFactory;
    protected $fillable=['title','name','phone1','phone2'];
}
