<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\AnnouncementResource;

class AnnouncementController extends Controller
{
    /**
     * Display announcements relevant to the parent.
     * (General announcements + announcements for their children's classes)
     */
    public function index()
    {
        $parent = Auth::user()->parentProfile;
        if (!$parent) {
            return response()->json(['error' => 'Parent profile not found.'], 404);
        }

        $childClassIds = $parent->children()->pluck('class_id')->unique()->filter()->toArray();

        $announcements = Announcement::with(['author', 'targetClass'])
            ->whereNull('target_class_id') // General announcements
            ->orWhereIn('target_class_id', $childClassIds) // Targeted to their children's classes
            ->latest('publish_date') // Order by latest
            ->get();

        return AnnouncementResource::collection($announcements);
    }

    /**
     * Display a specific announcement.
     */
    public function show(Announcement $announcement)
    {
        // Optional: Add authorization check if needed (e.g., is it general or for parent's class?)
        $announcement->load(['author', 'targetClass']);
        return new AnnouncementResource($announcement);
    }

    // store, update, destroy not needed for parents
}