<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supervisor extends Model
{
    use HasFactory;
    protected $fillable=['name'];


    public function revenue(){
        return Course::where('lead_id', '=', $this->id)->sum('price');
    }

    public function clients(){
        return Course::where('lead_id', $this->id)->distinct('company_id')->count('company_id');

    }

    public function courses(){
        return Course::where('lead_id', '=', $this->id)->count();
    }

    public function days(){
        return Course::where('lead_id', '=', $this->id)->sum('duration');
    }

    public function users(){
        return Course::where('lead_id', '=', $this->id)->get()->sum(function ($course){
            return $course->userCount();
        });
    }
}
