<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EducationalResource;
use Illuminate\Http\Request;
use App\Http\Resources\EducationalResource as EducationalResourceResource;

class EducationalResourceController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 15);

        $resources = EducationalResource::with('addedByUser') // تم تغيير اسم العلاقة
                     ->latest() // أو ->latest('created_at')  <-- التعديل هنا
                     ->paginate($perPage);

        return EducationalResourceResource::collection($resources);
    }

    public function show(EducationalResource $educationalResource)
    {
         $educationalResource->load('addedByUser'); // تم تغيير اسم العلاقة
         return new EducationalResourceResource($educationalResource);
    }
}