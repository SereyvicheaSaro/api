<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pass extends Model
{
    use HasFactory;

    protected $fillable = [
        'visitor_id',
        'approved_by',
        'status',
        'qr_code',
        'vehicle_plate',
    ];

    public function visitors()
    {
        return $this->belongsTo(Visitor::class);
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class);
    }



}
