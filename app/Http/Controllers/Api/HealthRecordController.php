<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HealthRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\HealthRecordResource; // Create this

class HealthRecordController extends Controller
{
    /**
     * Display health records for the parent's children.
     */
    public function index()
    {
        $parent = Auth::user()->parentProfile;
        if (!$parent) {
             return response()->json(['error' => 'Parent profile not found.'], 404);
        }

        $childIds = $parent->children()->pluck('child_id');

        if ($childIds->isEmpty()) {
            return HealthRecordResource::collection([]);
        }

        $healthRecords = HealthRecord::whereIn('child_id', $childIds)
                           ->orderBy('record_date', 'desc')
                           ->get();

        return HealthRecordResource::collection($healthRecords);
    }

    // show, store, update, destroy likely not needed for parents via API
    // (Maybe allow parents to add certain records? If so, implement store with validation/auth)
}