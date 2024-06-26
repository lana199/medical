<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = ['updated_at', 'created_at', 'id', 'is_active', 'image_path', 'mobile', 'gender', 'age', 'location'];

    public function Appointments()
    {
        return $this->hasMany(Appointment::class, 'id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }
}
