<?php

namespace App\Observers;

use App\Models\Visitor;

class VisitorObserver
{
    public function updating(Visitor $visitor)
    {
        // Check if scan_count is updated and has reached 2
        if ($visitor->isDirty('scan_count') && $visitor->scan_count >= 2) {
            $visitor->status = 'rejected'; // Change to 'pending' if that's your intended status
        }
    }
}
