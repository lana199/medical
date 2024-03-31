<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedPatient extends Model
{
    use HasFactory;

    protected $fillable = ['doctor_id', 'updated_at', 'created_at', 'id', 'patient_id'];


}
