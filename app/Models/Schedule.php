<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable=['course_id','date','from_time','end_time','trainer_id','fees', 'payment_method'];


    public function trainer(){
        return $this->belongsTo(Trainer::class,'trainer_id')->where('type', 3);
    }
}
