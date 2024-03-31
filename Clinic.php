<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'location', 'is_active', 'is_open', 'mobile', 'id', 'created_at', 'updated_at'];

    public function doctors()
    {
        return $this->hasMany(Doctor::class,'clinic_id');
    }
}
