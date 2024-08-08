<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    protected $fillable = [
        'visitor_id',
        'gate_id'
    ];

    public function visitor() {
        return $this->belongsTo(Visitor::class);
    }

    public function gate() {
        return $this->hasMany(Gate::class);
    }

}
