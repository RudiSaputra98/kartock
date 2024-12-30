<?php

namespace App\Services;

use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class LogService
{
    public static function logActivity($activityType, $entity, $description, $data)
    {
        Log::create([
            'user_id' => Auth::id(),
            'activity_type' => $activityType,
            'entity' => $entity,
            'description' => $description,
            'data' => json_encode($data),
        ]);
    }
}
