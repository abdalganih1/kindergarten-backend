<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WeeklySchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\WeeklyScheduleResource; // Create this

class ScheduleController extends Controller
{
    /**
     * Display schedules for the classes of the parent's children.
     */
    public function index()
    {
        $parent = Auth::user()->parentProfile;
        if (!$parent) {
            return response()->json(['error' => 'Parent profile not found.'], 404);
        }

        // Get class IDs of the parent's children
        $childClassIds = $parent->children()->pluck('class_id')->unique()->filter(); // filter removes nulls

        if ($childClassIds->isEmpty()) {
             return WeeklyScheduleResource::collection([]); // Return empty collection if no classes
        }

        $schedules = WeeklySchedule::whereIn('class_id', $childClassIds)
                       ->orderBy('day_of_week') // Consider ordering
                       ->orderBy('start_time')
                       ->get();

        return WeeklyScheduleResource::collection($schedules);
    }

     // show, store, update, destroy likely not needed for parents
}