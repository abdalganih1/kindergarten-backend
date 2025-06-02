<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Observation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Http\Resources\ObservationResource;
use App\Models\ParentModel; // يمكنك استيراده

class ObservationController extends Controller
{
    /**
     * Display observations submitted by the authenticated parent with pagination.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $parent = $user ? $user->parentProfile : null;

        if (!$parent) {
             return response()->json(['error' => 'Parent profile not found.'], 404);
        }

        $perPage = $request->query('per_page', 15);

        $observations = Observation::with(['child'])
                       ->where('parent_id', $parent->parent_id)
                       ->latest('submitted_at')
                       ->paginate($perPage);

        return ObservationResource::collection($observations);
    }

    /**
     * Store a new observation/feedback from the parent.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $parent = $user ? $user->parentProfile : null;

        if (!$parent) {
             return response()->json(['error' => 'Parent profile not found.'], 404);
        }

        // الحصول على معرفات أطفال ولي الأمر مع تحديد الجدول
        // ---=== تعديل هنا ===---
        $parentChildIds = $parent->children()->pluck('children.child_id');
        // ---=== نهاية التعديل ===---

        $validated = $request->validate([
            'observation_text' => ['required', 'string', 'max:2000'],
            'child_id' => [
                'nullable',
                'integer',
                // التأكد من أن الطفل (إذا تم تحديده) ينتمي لولي الأمر
                // ---=== تعديل هنا أيضًا لتحديد الجدول في whereIn إذا كان $parentChildIds عبارة عن Collection ===---
                Rule::exists('children', 'child_id')->where(function ($query) use ($parentChildIds) {
                    // إذا كان $parentChildIds هو Collection، استخدم ->all()
                    $query->whereIn('children.child_id', $parentChildIds->all());
                }),
                // ---=== نهاية التعديل ===---
             ],
        ]);

        $observation = Observation::create([
            'parent_id' => $parent->parent_id,
            'child_id' => $validated['child_id'] ?? null,
            'observation_text' => $validated['observation_text'],
        ]);

        return new ObservationResource($observation->load(['child', 'parentSubmitter']));
    }

    /**
     * Remove the specified observation if submitted by the authenticated parent.
     */
    public function destroy(Observation $observation)
    {
        $user = Auth::user();
        $parent = $user ? $user->parentProfile : null;

        if (!$parent || $observation->parent_id !== $parent->parent_id) {
            return response()->json(['error' => 'Unauthorized to delete this observation.'], 403);
        }

        $observation->delete();

        return response()->json(['message' => 'Observation deleted successfully.'], 200);
    }
}