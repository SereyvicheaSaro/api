<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Visitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'contact', 
        'purpose', 
        'entry_time', 
        'exit_time', 
        'scan_count', 
        'date', 
        'approver', 
        'status'
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
