<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'contact',
        'avatar',
        'bio',
    ];

    // public function passes()
    // {
    //     return $this->hasMany(Pass::class);
    // }
    
}
