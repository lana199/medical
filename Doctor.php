<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'gender', 'specialist_id', 'clinic_id', 'mobile', 'is_active', 'image_path', 'updated_at', 'created_at', 'session_duration'];

    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }

    public function specialist()
    {
        return $this->belongsTo(Specialist::class, 'specialist_id');
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id');
    }

    public function Appointments()
    {
        return $this->hasMany(Appointment::class, 'id');
    }



}
