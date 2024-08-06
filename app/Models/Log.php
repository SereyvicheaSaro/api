<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    protected $fillable = [
        'visitor_id',
        'entry_time',
        'exit_time',
    ];

    public function visitor()
    {
        return $this->belongsTo(Visitor::class);
    }

}
