<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoursesTrainee extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable=['course_id','user_id','price'];

    public function payments()
    {
        return Payment::where('course_id', $this->course_id)->where('entity_id', $this->user_id)->where('entity_type', 'trainee')->get();
    }

}
