<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact',
        'purpos',
        'entry_time',
        'exit_time',
    ];
    
    public function passes()
    {
        return $this->hasMany(Pass::class);
    }

    public function logs()
    {
        return $this->hasMany(Log::class);
    }

}
