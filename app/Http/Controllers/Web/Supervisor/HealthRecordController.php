<?php

namespace App\Http\Controllers\Web\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\HealthRecord;
use App\Models\Child; // للفلترة واختيار الطفل
use App\Models\KindergartenClass; // للفلترة
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // للتعامل مع رفع الملفات (المستندات)
use Illuminate\Validation\Rule;
use App\Http\Requests\Supervisor\StoreHealthRecordRequest; // يجب إنشاؤه
use App\Http\Requests\Supervisor\UpdateHealthRecordRequest; // يجب إنشاؤه
use Illuminate\Pagination\LengthAwarePaginator; // لاستخدام مقسم الصفحات اليدوي
use Illuminate\Support\Collection; // لاستخدام Collection
class HealthRecordController extends Controller
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
     * Display a listing of health records for children in supervised classes.
     */
    public function index(Request $request)
    {
        $supervisorClassIds = $this->getSupervisorClassIds();

        if ($supervisorClassIds->isEmpty()) {
            return view('web.supervisor.health_records.index', [
               'healthRecords' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
               'children' => collect(),
               'recordTypes' => [],
               'selectedChildId' => null,
               'selectedType' => null,
               'noClassesAssigned' => true
           ]);
        }

        $query = HealthRecord::with(['child.kindergartenClass', 'enteredByUser'])
                             ->select('health_records.*') // تحديد الجدول الأساسي
                             ->join('children', 'health_records.child_id', '=', 'children.child_id')
                             ->whereIn('children.class_id', $supervisorClassIds); // *** فلترة حسب فصول المشرف ***

        // --- الفلترة ---
        // 1. حسب الطفل
        $selectedChildId = $request->query('child_id');
        if ($selectedChildId) {
            // التأكد من أن الطفل المختار ينتمي لفصل يشرف عليه
            $childExists = Child::where('child_id', $selectedChildId)
                                ->whereIn('class_id', $supervisorClassIds)
                                ->exists();
            if ($childExists) {
                $query->where('health_records.child_id', $selectedChildId);
            } else {
                 // منع عرض أي شيء إذا حاول اختيار طفل غير مسموح به
                $query->whereRaw('1 = 0');
            }
        }

        // 2. حسب نوع السجل
        $selectedType = $request->query('record_type');
        if ($selectedType) {
            $query->where('record_type', $selectedType);
        }

        // --- الترتيب والـ Pagination ---
        $healthRecords = $query->latest('health_records.record_date') // الأحدث حسب تاريخ السجل
                               ->paginate(15)
                               ->withQueryString();

        // --- بيانات إضافية للـ View ---
        // جلب الأطفال من الفصول التي يشرف عليها فقط
        $children = Child::whereIn('class_id', $supervisorClassIds)
                         ->orderBy('first_name')->get(['child_id', 'first_name', 'last_name'])
                         ->mapWithKeys(fn($child) => [$child->child_id => $child->full_name]);

        $recordTypes = ['Vaccination'=>'تطعيم', 'Checkup'=>'فحص طبي', 'Illness'=>'مرض/إصابة', 'MedicationAdministered'=>'دواء تم إعطاؤه'];

        return view('web.supervisor.health_records.index', compact(
            'healthRecords',
            'children',
            'recordTypes',
            'selectedChildId',
            'selectedType'
        ));
    }

    /**
     * Show the form for creating a new health record.
     */
    public function create(Request $request)
    {
        $supervisorClassIds = $this->getSupervisorClassIds();
         if ($supervisorClassIds->isEmpty()) {
             return redirect()->route('supervisor.health-records.index')->with('error', 'لا يمكنك إضافة سجلات صحية لعدم وجود فصول معينة لك.');
         }

        // جلب الأطفال من الفصول التي يشرف عليها المشرف
        $children = Child::whereIn('class_id', $supervisorClassIds)
                         ->orderBy('first_name')
                         ->get(['child_id', 'first_name', 'last_name'])
                         ->mapWithKeys(fn($child) => [$child->child_id => $child->full_name]);

         // إذا لم يكن هناك أطفال في فصوله
         if ($children->isEmpty()) {
              return redirect()->route('supervisor.health-records.index')->with('warning', 'لا يوجد أطفال في الفصول التي تشرف عليها لإضافة سجل صحي لهم.');
         }

        $recordTypes = ['Vaccination'=>'تطعيم', 'Checkup'=>'فحص طبي', 'Illness'=>'مرض/إصابة', 'MedicationAdministered'=>'دواء تم إعطاؤه'];
        // تحديد طفل افتراضي إذا جاء من رابط معين
        $selectedChildId = $request->query('child_id');

        return view('web.supervisor.health_records.create', compact('children', 'recordTypes', 'selectedChildId'));
    }

    /**
     * Store a newly created health record in storage.
     */
    public function store(StoreHealthRecordRequest $request) 
    {
        $validated = $request->validated();
        $supervisorClassIds = $this->getSupervisorClassIds();

        // *** التحقق من الصلاحية: هل الطفل المختار ضمن فصول المشرف؟ ***
        $child = Child::find($validated['child_id']);
        if (!$child || !$supervisorClassIds->contains($child->class_id)) {
             return back()->with('error', 'لا يمكنك إضافة سجل لهذا الطفل.')->withInput();
        }

        $documentPath = null;
        // التعامل مع رفع ملف المستند
        if ($request->hasFile('document') && $request->file('document')->isValid()) {
            // تعريف أنواع الملفات المسموحة (يمكن توسيعها)
            // $allowedMimes = ['pdf', 'jpg', 'jpeg', 'png'];
            // $validated['document'] = $request->validate(['document' => ['file', 'mimes:' . implode(',', $allowedMimes), 'max:5120']]); // Max 5MB
            $documentPath = $request->file('document')->store('health_documents', 'public'); // Run: php artisan storage:link
        }

        HealthRecord::create([
            'child_id' => $validated['child_id'],
            'record_type' => $validated['record_type'],
            'record_date' => $validated['record_date'],
            'details' => $validated['details'],
            'next_due_date' => $validated['next_due_date'] ?? null,
            'document_path' => $documentPath,
            'entered_by_id' => Auth::id(), // هوية المشرف الحالي
        ]);

        return redirect()->route('supervisor.health-records.index', ['child_id' => $validated['child_id']])
                         ->with('success', 'تم إضافة السجل الصحي بنجاح.');
    }

    /**
     * Display the specified health record. (Optional)
     */
    public function show(HealthRecord $healthRecord)
    {
         // *** التحقق من الصلاحية ***
        $supervisorClassIds = $this->getSupervisorClassIds();
        if (!$supervisorClassIds->contains($healthRecord->child->class_id)) {
            abort(403);
        }
        $healthRecord->load(['child.kindergartenClass', 'enteredByUser']);
        return view('web.supervisor.health_records.show', compact('healthRecord'));
    }

    /**
     * Show the form for editing the specified health record.
     */
    public function edit(HealthRecord $healthRecord)
    {
         // *** التحقق من الصلاحية ***
        $supervisorClassIds = $this->getSupervisorClassIds();
        if (!$supervisorClassIds->contains($healthRecord->child->class_id)) {
            abort(403);
        }

        // لا نحتاج عادة لتغيير الطفل عند التعديل، لكن نحتاج قائمة الأنواع
        $recordTypes = ['Vaccination'=>'تطعيم', 'Checkup'=>'فحص طبي', 'Illness'=>'مرض/إصابة', 'MedicationAdministered'=>'دواء تم إعطاؤه'];
        // جلب الطفل المرتبط لعرض اسمه
        $healthRecord->load('child');

        return view('web.supervisor.health_records.edit', compact('healthRecord', 'recordTypes'));
    }

    /**
     * Update the specified health record in storage.
     */
    public function update(UpdateHealthRecordRequest $request, HealthRecord $healthRecord)
    {
         // *** التحقق من الصلاحية (يمكن نقله لـ authorize في Form Request) ***
        $supervisorClassIds = $this->getSupervisorClassIds();
        if (!$supervisorClassIds->contains($healthRecord->child->class_id)) {
            abort(403);
        }

        $validated = $request->validated();
        $documentPath = $healthRecord->document_path; // الاحتفاظ بالمسار القديم افتراضيًا

        // التعامل مع رفع ملف مستند جديد
        if ($request->hasFile('document') && $request->file('document')->isValid()) {
            // حذف المستند القديم إذا وجد
            if ($healthRecord->document_path && Storage::disk('public')->exists($healthRecord->document_path)) {
                Storage::disk('public')->delete($healthRecord->document_path);
            }
            $documentPath = $request->file('document')->store('health_documents', 'public');
        } elseif ($request->boolean('remove_document')) { // خيار لإزالة المستند
             if ($healthRecord->document_path && Storage::disk('public')->exists($healthRecord->document_path)) {
                Storage::disk('public')->delete($healthRecord->document_path);
            }
            $documentPath = null;
        }

        // تحديث السجل
        $healthRecord->update([
            // لا نغير child_id عادةً
            'record_type' => $validated['record_type'],
            'record_date' => $validated['record_date'],
            'details' => $validated['details'],
            'next_due_date' => $validated['next_due_date'] ?? null,
            'document_path' => $documentPath,
             // يمكن تحديث entered_by_id إذا أردنا معرفة من قام بآخر تعديل
             // 'entered_by_id' => Auth::id(),
        ]);

        return redirect()->route('supervisor.health-records.index', ['child_id' => $healthRecord->child_id])
                         ->with('success', 'تم تحديث السجل الصحي بنجاح.');
    }

    /**
     * Remove the specified health record from storage.
     */
    public function destroy(HealthRecord $healthRecord)
    {
        // *** التحقق من الصلاحية ***
        $supervisorClassIds = $this->getSupervisorClassIds();
        if (!$supervisorClassIds->contains($healthRecord->child->class_id)) {
             abort(403);
        }

        try {
            $childId = $healthRecord->child_id; // تخزين ID الطفل قبل الحذف

            // حذف المستند المرفق إن وجد
            if ($healthRecord->document_path && Storage::disk('public')->exists($healthRecord->document_path)) {
                Storage::disk('public')->delete($healthRecord->document_path);
            }

            // حذف السجل
            $healthRecord->delete();

            return redirect()->route('supervisor.health-records.index', ['child_id' => $childId])
                             ->with('success', 'تم حذف السجل الصحي بنجاح.');
        } catch (\Exception $e) {
             \Log::error("Health record deletion failed for ID {$healthRecord->record_id}: " . $e->getMessage());
            return redirect()->route('supervisor.health-records.index')
                             ->with('error', 'فشل حذف السجل الصحي. يرجى المحاولة مرة أخرى.');
        }
    }
}