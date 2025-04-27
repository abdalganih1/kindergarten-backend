<?php

namespace App\Http\Controllers\Web\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\KindergartenClass;
use App\Models\Child;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
// استخدام نفس Form Requests الخاصة بالمدير قد يكون مناسبًا مع تعديل authorize
use App\Http\Requests\Admin\StoreMediaRequest as SupervisorStoreMediaRequest;

class MediaController extends Controller
{
    /**
     * Get the classes supervised by the current user.
     */
    private function getSupervisorClassIds()
    {
        $user = Auth::user();
        // TODO: Implement supervisor class scoping correctly
        return KindergartenClass::pluck('class_id'); // Example: All classes for now
        // return $user->supervisorClasses()->pluck('class_id');
    }

    /**
     * Display a listing of media relevant to the supervisor's classes.
     */
    public function index(Request $request)
    {
        $supervisorClassIds = $this->getSupervisorClassIds();

        if ($supervisorClassIds->isEmpty()) {
             return view('web.supervisor.media.index', [
               'mediaItems' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12),
               'mediaTypes' => [],
               'supervisedClasses' => collect(),
               'mediaType' => null, 'classId' => null, 'childId' => null, 'eventId' => null, // Initialize filter vars
               'noClassesAssigned' => true
           ]);
        }

        // عرض الوسائط المرتبطة بفصول المشرف أو التي رفعها المشرف بنفسه
        $query = Media::with(['uploader', 'associatedChild', 'associatedEvent', 'associatedClass'])
                       ->where(function($q) use ($supervisorClassIds) {
                           $q->whereIn('associated_class_id', $supervisorClassIds) // مرتبطة بفصوله
                             ->orWhere('uploader_id', Auth::id()); // أو رفعها هو
                       });


        // --- الفلترة (مشابهة للمدير ولكن ضمن نطاق المشرف) ---
        $mediaType = $request->query('media_type');
        if ($mediaType) {
            $query->where('media_type', $mediaType);
        }
        $classId = $request->query('class_id');
        if ($classId === 'none') { // فلتر "غير مرتبط بفصل"
            $query->whereNull('associated_class_id');
        } elseif ($classId && $supervisorClassIds->contains($classId)) { // فلتر فصل معين (من فصوله)
             $query->where('associated_class_id', $classId);
        } elseif ($classId) { // حاول اختيار فصل ليس له صلاحية عليه
            $query->whereRaw('1 = 0');
        }

        // TODO: Add filtering by child/event within supervised classes if needed

        // --- الترتيب والـ Pagination ---
        $mediaItems = $query->latest('upload_date')
                           ->paginate(12)
                           ->withQueryString();

        // --- بيانات إضافية للـ View ---
        $mediaTypes = ['Image' => 'صور', 'Video' => 'فيديو'];
        $supervisedClasses = KindergartenClass::whereIn('class_id', $supervisorClassIds)->orderBy('class_name')->pluck('class_name', 'class_id');


        return view('web.supervisor.media.index', compact(
            'mediaItems',
            'mediaTypes',
            'supervisedClasses',
            'mediaType',
            'classId'
            // Pass other filter variables if added: 'childId', 'eventId'
        ));
    }

    /**
     * Show the form for creating (uploading) new media resources.
     */
    public function create()
    {
        $supervisorClassIds = $this->getSupervisorClassIds();
         if ($supervisorClassIds->isEmpty()) {
             return redirect()->route('supervisor.media.index')->with('error', 'لا يمكنك رفع وسائط لعدم وجود فصول معينة لك.');
         }

        // جلب البيانات اللازمة للربط (مقيدة بفصول المشرف)
        $classes = KindergartenClass::whereIn('class_id', $supervisorClassIds)->orderBy('class_name')->pluck('class_name', 'class_id');
        $children = Child::whereIn('class_id', $supervisorClassIds)->orderBy('first_name')->get(['child_id', 'first_name', 'last_name']);
        // الفعاليات: يمكن عرض كل الفعاليات القادمة أو ربما فلترتها أكثر
        $events = Event::where('event_date', '>=', now())->orderBy('event_date')->pluck('event_name', 'event_id');

        return view('web.supervisor.media.create', compact('classes', 'children', 'events'));
    }

    /**
     * Store newly created media resources uploaded by the supervisor.
     */
    public function store(SupervisorStoreMediaRequest $request) // استخدام Form Request
    {
        $validated = $request->validated();
        $supervisorClassIds = $this->getSupervisorClassIds(); // للحقق من صلاحية الربط
        $uploadedCount = 0;
        $errors = [];

        // *** التحقق من صلاحية الربط قبل الرفع ***
        if (($validated['associated_class_id'] ?? null) && !$supervisorClassIds->contains($validated['associated_class_id'])) {
             return back()->with('error', 'لا يمكنك ربط الوسائط بهذا الفصل.')->withInput();
        }
        if ($validated['associated_child_id'] ?? null) {
            $childClass = Child::find($validated['associated_child_id'])?->class_id;
            if (!$childClass || !$supervisorClassIds->contains($childClass)) {
                 return back()->with('error', 'لا يمكنك ربط الوسائط بهذا الطفل.')->withInput();
            }
        }
        // يمكنك إضافة تحقق للفعالية إذا لزم الأمر

        // --- منطق الرفع (مشابه للمدير) ---
        if ($request->hasFile('media_files')) {
            foreach ($request->file('media_files') as $file) {
                 if ($file->isValid()) {
                    try {
                        $mimeType = $file->getMimeType();
                        $mediaType = str_starts_with($mimeType, 'image/') ? 'Image' : (str_starts_with($mimeType, 'video/') ? 'Video' : null);

                        if ($mediaType === null) {
                            $errors[] = "File '{$file->getClientOriginalName()}' type ({$mimeType}) not supported.";
                            continue;
                        }

                        $filePath = $file->store('media', 'public');

                        Media::create([
                            'file_path' => $filePath,
                            'media_type' => $mediaType,
                            'description' => $validated['description'] ?? null,
                            'uploader_id' => Auth::id(), // هوية المشرف الحالي
                            'associated_child_id' => $validated['associated_child_id'] ?? null,
                            'associated_event_id' => $validated['associated_event_id'] ?? null,
                            'associated_class_id' => $validated['associated_class_id'] ?? null,
                        ]);
                        $uploadedCount++;

                    } catch (\Exception $e) {
                        $errors[] = "Upload failed for '{$file->getClientOriginalName()}': " . $e->getMessage();
                        \Log::error("Supervisor Media upload error: " . $e->getMessage());
                    }
                 } else {
                      $errors[] = "File '{$file->getClientOriginalName()}' is not valid.";
                 }
            }
        } else {
             return back()->with('error', 'No files selected.')->withInput();
        }

        // تحديد رسالة الرد
        $message = $uploadedCount . " file(s) uploaded.";
        if (!empty($errors)) {
            $message .= " Errors: " . implode('; ', $errors);
            return redirect()->route('supervisor.media.index')->with('warning', $message);
        }
        return redirect()->route('supervisor.media.index')->with('success', $message);
    }


    /**
     * Show the form for editing media (if supervisor has permission).
     */
    public function edit(Media $medium) // اسم المتغير $medium
    {
         // *** التحقق من الصلاحية: هل الوسائط مرتبطة بفصل مشرف عليه أو رفعها هو؟ ***
        $supervisorClassIds = $this->getSupervisorClassIds();
        $canEdit = $supervisorClassIds->contains($medium->associated_class_id) || $medium->uploader_id === Auth::id();

        if (!$canEdit) {
            abort(403, 'Unauthorized access to edit this media.');
        }

        // جلب البيانات اللازمة للربط (مقيدة بفصول المشرف للأطفال والفصول)
        $classes = KindergartenClass::whereIn('class_id', $supervisorClassIds)->orderBy('class_name')->pluck('class_name', 'class_id');
        $children = Child::whereIn('class_id', $supervisorClassIds)->orderBy('first_name')->get(['child_id', 'first_name', 'last_name']);
        $events = Event::orderBy('event_date', 'desc')->pluck('event_name', 'event_id');

        return view('web.supervisor.media.edit', compact('medium', 'classes', 'children', 'events'));
    }

    /**
     * Update the specified media resource (description/associations) (if supervisor has permission).
     */
    public function update(Request $request, Media $medium) // استخدام Request أبسط للتحديث
    {
        // *** التحقق من الصلاحية ***
        $supervisorClassIds = $this->getSupervisorClassIds();
        $canUpdate = $supervisorClassIds->contains($medium->associated_class_id) || $medium->uploader_id === Auth::id();
        if (!$canUpdate) {
            abort(403);
        }

        $validated = $request->validate([
            'description' => ['nullable', 'string', 'max:2000'],
            'associated_child_id' => ['nullable', 'integer', Rule::exists('children', 'child_id')->whereIn('class_id', $supervisorClassIds)], // التحقق من أن الطفل ضمن فصول المشرف
            'associated_event_id' => ['nullable', 'integer', 'exists:events,event_id'],
            'associated_class_id' => ['nullable', 'integer', Rule::exists('kindergarten_classes', 'class_id')->whereIn('class_id', $supervisorClassIds)], // التحقق من أن الفصل ضمن فصول المشرف
        ]);

        // تعيين القيم الفارغة إلى null
        $validated['associated_child_id'] = $validated['associated_child_id'] ?: null;
        $validated['associated_event_id'] = $validated['associated_event_id'] ?: null;
        $validated['associated_class_id'] = $validated['associated_class_id'] ?: null;

        $medium->update($validated);

        return redirect()->route('supervisor.media.index')->with('success', 'تم تحديث بيانات الوسائط بنجاح.');
    }

    /**
     * Remove the specified media resource (if supervisor has permission).
     */
    public function destroy(Media $medium)
    {
        // *** التحقق من الصلاحية ***
        $supervisorClassIds = $this->getSupervisorClassIds();
        $canDelete = $supervisorClassIds->contains($medium->associated_class_id) || $medium->uploader_id === Auth::id();
         if (!$canDelete) {
             abort(403);
        }

        try {
            if (Storage::disk('public')->exists($medium->file_path)) {
                Storage::disk('public')->delete($medium->file_path);
            }
            $medium->delete();
            return redirect()->route('supervisor.media.index')
                             ->with('success', 'تم حذف الوسائط بنجاح.');
        } catch (\Exception $e) {
            \Log::error("Supervisor Media deletion error for ID {$medium->media_id}: " . $e->getMessage());
             return redirect()->route('supervisor.media.index')
                             ->with('error', 'فشل حذف الوسائط.');
        }
    }

     // الدالة show قد لا تكون ضرورية لواجهة الويب للمشرف
}