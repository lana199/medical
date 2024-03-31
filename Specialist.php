<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialist extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'name', 'is_active', 'created_at', 'updated_at'];

    public function doctors()
    {
        return $this->hasMany(Doctor::class,'specialist_id');
    }


}
