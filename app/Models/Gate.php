<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gate extends Model{

    protected $fillable = [
        'code',
        'location',    
    ];
    
}