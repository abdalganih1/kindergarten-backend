<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Child;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ChildResource; // Create this resource

class ChildController extends Controller
{
    /**
     * Display a listing of the parent's children.
     */
    public function index()
    {
        $parent = Auth::user()->parentProfile; // Assumes parentProfile relationship exists on User model

        if (!$parent) {
            return response()->json(['error' => 'Parent profile not found.'], 404);
        }

        // Eager load class for efficiency
        $children = $parent->children()->with('kindergartenClass')->get();

        return ChildResource::collection($children);
    }

    /**
     * Display the specified child (if owned by the parent).
     */
    public function show(Child $child) // Route model binding
    {
         $parent = Auth::user()->parentProfile;

        // Authorization Check: Ensure the parent owns this child
        if (!$parent || !$parent->children()->where('children.child_id', $child->child_id)->exists()) {
             return response()->json(['error' => 'Unauthorized or Child not found.'], 403);
        }

        // Eager load necessary relationships
        $child->load(['kindergartenClass', 'healthRecords', 'attendances']); // Add more relations as needed

        return new ChildResource($child);
    }

    // store, update, destroy are usually not needed for parents via API
}