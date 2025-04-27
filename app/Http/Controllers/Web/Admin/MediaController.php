<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\KindergartenClass; // للربط بالفصول
use App\Models\Child;             // للربط بالأطفال
use App\Models\Event;              // للربط بالفعاليات
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;      // للحصول على هوية المستخدم
use Illuminate\Support\Facades\Storage; // للتعامل مع الملفات
use App\Http\Requests\Admin\StoreMediaRequest; // يجب إنشاؤه (يمكن استخدامه للتحديث أيضًا)

class MediaController extends Controller
{
    /**
     * Display a listing of the media resources.
     * عرض قائمة بالوسائط مع الفلترة.
     */
    public function index(Request $request)
    {
        $query = Media::with(['uploader', 'associatedChild', 'associatedEvent', 'associatedClass']);

        // --- الفلترة ---
        // 1. حسب نوع الوسائط
        $mediaType = $request->query('media_type');
        if ($mediaType) {
            $query->where('media_type', $mediaType);
        }

        // 2. حسب الفصل المرتبط
        $classId = $request->query('class_id');
        if ($classId) {
            $query->where('associated_class_id', $classId);
        }

        // 3. حسب الطفل المرتبط
        $childId = $request->query('child_id');
        if ($childId) {
            $query->where('associated_child_id', $childId);
        }

        // 4. حسب الفعالية المرتبطة
        $eventId = $request->query('event_id');
        if ($eventId) {
            $query->where('associated_event_id', $eventId);
        }

        // --- الترتيب والـ Pagination ---
        $mediaItems = $query->latest('upload_date') // الأحدث أولاً
                           ->paginate(12) // عرض 12 عنصر في الصفحة (مناسب للصور)
                           ->withQueryString();

        // --- بيانات إضافية للـ View (للفلاتر) ---
        $mediaTypes = ['Image' => 'صور', 'Video' => 'فيديو'];
        $classes = KindergartenClass::orderBy('class_name')->pluck('class_name', 'class_id');
        // يمكن جلب قائمة الأطفال والفعاليات أيضًا إذا كانت الفلاتر ستكون قوائم منسدلة

        return view('web.admin.media.index', compact(
            'mediaItems',
            'mediaTypes',
            'classes',
            'mediaType',
            'classId',
            'childId', // تمرير قيم الفلاتر الحالية
            'eventId'
        ));
    }

    /**
     * Show the form for creating (uploading) new media resources.
     * عرض نموذج رفع ملفات وسائط جديدة.
     */
    public function create()
    {
        // جلب البيانات اللازمة للربط الاختياري
        $classes = KindergartenClass::orderBy('class_name')->pluck('class_name', 'class_id');
        $children = Child::orderBy('first_name')->get(['child_id', 'first_name', 'last_name']); // جلب الأطفال للاختيار
        $events = Event::where('event_date', '>=', now())->orderBy('event_date')->pluck('event_name', 'event_id'); // الفعاليات القادمة

        return view('web.admin.media.create', compact('classes', 'children', 'events'));
    }

    /**
     * Store newly created media resources in storage.
     * تخزين ملفات الوسائط المرفوعة. يمكن رفع ملف واحد أو أكثر.
     *
     * @param  \App\Http\Requests\Admin\StoreMediaRequest  $request
     */
    public function store(StoreMediaRequest $request)
    {
        $validated = $request->validated();
        $uploadedCount = 0;
        $errors = [];

        if ($request->hasFile('media_files')) {
            foreach ($request->file('media_files') as $file) {
                if ($file->isValid()) {
                    try {
                        // تحديد نوع الوسائط بناءً على MIME Type
                        $mimeType = $file->getMimeType();
                        $mediaType = str_starts_with($mimeType, 'image/') ? 'Image' : (str_starts_with($mimeType, 'video/') ? 'Video' : null);

                        // تخطي الملفات غير المدعومة (اختياري)
                        if ($mediaType === null) {
                            $errors[] = "File '{$file->getClientOriginalName()}' has an unsupported type ({$mimeType}).";
                            continue;
                        }

                        // تخزين الملف في المجلد 'media' داخل القرص العام 'public'
                        $filePath = $file->store('media', 'public'); // Run: php artisan storage:link

                        // إنشاء سجل في قاعدة البيانات
                        Media::create([
                            'file_path' => $filePath,
                            'media_type' => $mediaType,
                            'description' => $validated['description'] ?? null,
                            'uploader_id' => Auth::id(), // هوية المستخدم الحالي
                            'associated_child_id' => $validated['associated_child_id'] ?? null,
                            'associated_event_id' => $validated['associated_event_id'] ?? null,
                            'associated_class_id' => $validated['associated_class_id'] ?? null,
                            // upload_date يتم تعيينها تلقائيًا
                        ]);
                        $uploadedCount++;

                    } catch (\Exception $e) {
                        $errors[] = "Could not upload file '{$file->getClientOriginalName()}': " . $e->getMessage();
                        \Log::error("Media upload error: " . $e->getMessage());
                        // يمكنك حذف الملف إذا تم رفعه ولكن فشل حفظ السجل إذا أردت
                        // if (isset($filePath) && Storage::disk('public')->exists($filePath)) {
                        //     Storage::disk('public')->delete($filePath);
                        // }
                    }
                } else {
                    $errors[] = "File '{$file->getClientOriginalName()}' is not valid.";
                }
            }
        } else {
            return back()->with('error', 'No files were selected for upload.')->withInput();
        }

        // تحديد رسالة الرد
        $message = $uploadedCount . " file(s) uploaded successfully.";
        if (!empty($errors)) {
            $message .= " However, some errors occurred: " . implode('; ', $errors);
            return redirect()->route('admin.media.index')->with('warning', $message); // استخدام warning لوجود أخطاء
        }

        return redirect()->route('admin.media.index')->with('success', $message);
    }

    /**
     * Display the specified media resource. (Optional - often done via modal in index)
     * عرض تفاصيل وسائط محددة (عادة ما يتم عرضها في نافذة منبثقة في صفحة القائمة).
     *
     * @param  \App\Models\Media  $medium // اسم المتغير مطابق للمسار
     */
    public function show(Media $medium)
    {
        $medium->load(['uploader', 'associatedChild', 'associatedEvent', 'associatedClass']);
        // قد ترغب في عرض واجهة بسيطة أو إعادة توجيه إلى الملف مباشرة
        // return Storage::disk('public')->response($medium->file_path); // يعرض الملف مباشرة
        return view('web.admin.media.show', compact('medium'));
    }

    /**
     * Show the form for editing the specified media resource (mainly description/associations).
     * عرض نموذج تعديل بيانات وسائط (الوصف والربط).
     *
     * @param  \App\Models\Media  $medium
     */
    public function edit(Media $medium)
    {
        // جلب البيانات اللازمة للربط
        $classes = KindergartenClass::orderBy('class_name')->pluck('class_name', 'class_id');
        $children = Child::orderBy('first_name')->get(['child_id', 'first_name', 'last_name']);
        $events = Event::orderBy('event_date', 'desc')->pluck('event_name', 'event_id'); // كل الفعاليات للتعديل

        return view('web.admin.media.edit', compact('medium', 'classes', 'children', 'events'));
    }

    /**
     * Update the specified media resource in storage (description/associations).
     * تحديث بيانات الوسائط (الوصف والربط). لا يتم تحديث الملف نفسه هنا.
     *
     * @param  \Illuminate\Http\Request  $request // يمكن استخدام StoreMediaRequest إذا كانت القواعد مناسبة
     * @param  \App\Models\Media  $medium
     */
    public function update(Request $request, Media $medium) // استخدام Request أبسط هنا
    {
         $validated = $request->validate([
            'description' => ['nullable', 'string', 'max:2000'],
            'associated_child_id' => ['nullable', 'integer', 'exists:children,child_id'],
            'associated_event_id' => ['nullable', 'integer', 'exists:events,event_id'],
            'associated_class_id' => ['nullable', 'integer', 'exists:kindergarten_classes,class_id'],
        ]);

        // تعيين القيم الفارغة المرسلة من select إلى null
        $validated['associated_child_id'] = $validated['associated_child_id'] ?: null;
        $validated['associated_event_id'] = $validated['associated_event_id'] ?: null;
        $validated['associated_class_id'] = $validated['associated_class_id'] ?: null;

        $medium->update($validated);

        return redirect()->route('admin.media.index')->with('success', 'تم تحديث بيانات الوسائط بنجاح.');
    }

    /**
     * Remove the specified media resource from storage and database.
     * حذف الوسائط من التخزين وقاعدة البيانات.
     *
     * @param  \App\Models\Media  $medium
     */
    public function destroy(Media $medium)
    {
        try {
            // 1. حذف الملف الفعلي من التخزين
            if (Storage::disk('public')->exists($medium->file_path)) {
                Storage::disk('public')->delete($medium->file_path);
            }

            // 2. حذف سجل قاعدة البيانات
            $medium->delete();

            return redirect()->route('admin.media.index')
                             ->with('success', 'تم حذف الوسائط بنجاح.');
        } catch (\Exception $e) {
            \Log::error("Media deletion error for ID {$medium->media_id}: " . $e->getMessage());
             return redirect()->route('admin.media.index')
                             ->with('error', 'فشل حذف الوسائط. يرجى المحاولة مرة أخرى.');
        }
    }
}