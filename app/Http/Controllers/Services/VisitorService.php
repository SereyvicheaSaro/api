<?php

namespace App\Http\Controllers\Services;

use App\Models\Visitor;
use Illuminate\Support\Carbon;

class VisitorService
{
    protected $scanLimit = 2; // Define the scan limit

    public function updateStatusBasedOnScanCount(Visitor $visitor)
    {
        // Check if the scan count exceeds the limit
        if ($visitor->scan_count > $this->scanLimit) {
            $visitor->status = 'expired';
            $visitor->save();
        }
    }
}
