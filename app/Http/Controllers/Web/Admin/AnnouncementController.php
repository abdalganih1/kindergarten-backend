<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\KindergartenClass; // لاستخدامه في نماذج الإنشاء والتعديل
use App\Models\User; // إذا كنت تريد إرسال إشعارات للمستخدمين
use App\Notifications\NewAnnouncementNotification; // إنشاء هذا الإشعار
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // للحصول على هوية المدير الذي أنشأ الإعلان
use Illuminate\Support\Facades\Notification; // لإرسال الإشعارات
use App\Http\Requests\Admin\StoreAnnouncementRequest; // استخدم Form Request للتحقق
use App\Http\Requests\Admin\UpdateAnnouncementRequest; // استخدم Form Request للتحقق

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the announcements.
     * يعرض قائمة بجميع الإعلانات مع إمكانية الفلترة والبحث (يمكن إضافتها لاحقًا).
     */
    public function index()
    {
        // جلب الإعلانات مع تحميل علاقات المؤلف والفصل المستهدف
        // الترتيب حسب الأحدث (يمكن تغييرها)
        // تطبيق Pagination
        $announcements = Announcement::with(['author.user', 'targetClass']) // author.user لجلب اسم المستخدم للمؤلف
                                     ->latest('publish_date') // أو latest() للترتيب حسب created_at
                                     ->paginate(15); // عرض 15 إعلانًا في الصفحة

        // إرسال البيانات إلى الواجهة (View)
        return view('web.admin.announcements.index', compact('announcements')); // تأكد من إنشاء هذا الـ view
    }

    /**
     * Show the form for creating a new announcement.
     * يعرض نموذج إدخال بيانات إعلان جديد.
     */
    public function create()
    {
        // جلب قائمة بالفصول لعرضها في قائمة منسدلة لاختيار الفصل المستهدف
        $classes = KindergartenClass::orderBy('class_name')->pluck('class_name', 'class_id');

        // إرسال قائمة الفصول إلى الواجهة
        return view('web.admin.announcements.create', compact('classes')); // تأكد من إنشاء هذا الـ view
    }

    /**
     * Store a newly created announcement in storage.
     * يخزن الإعلان الجديد في قاعدة البيانات.
     *
     * @param  \App\Http\Requests\Admin\StoreAnnouncementRequest  $request
     */
    public function store(StoreAnnouncementRequest $request) // استخدام Form Request
    {
        // الحصول على البيانات المتحقق منها من الـ Form Request
        $validated = $request->validated();

        // الحصول على هوية المدير المسجل دخوله
        $admin = Auth::user()->adminProfile; // افتراض وجود علاقة adminProfile
        if (!$admin) {
            // معالجة الحالة إذا لم يكن المستخدم مديرًا (نظريًا لن تحدث بسبب middleware)
            return back()->with('error', 'Admin profile not found.');
        }

        // إنشاء الإعلان
        $announcement = Announcement::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'author_id' => $admin->admin_id,
            // إذا تم اختيار "All Classes"، قيمة target_class_id ستكون null تلقائيًا
            'target_class_id' => $validated['target_class_id'] ?? null,
            'publish_date' => now(), // أو يمكن جعله حقلاً في النموذج للسماح بالنشر المستقبلي
        ]);

        // --- (اختياري) إرسال إشعارات لأولياء الأمور ---
        $targetUsers = User::query();

        if ($announcement->target_class_id) {
            // إيجاد أولياء أمور الأطفال في الفصل المستهدف فقط
            $targetUsers->whereHas('parentProfile.children', function ($query) use ($announcement) {
                $query->where('class_id', $announcement->target_class_id);
            });
        } else {
            // إيجاد جميع أولياء الأمور إذا كان الإعلان عامًا
            $targetUsers->where('role', 'Parent');
        }

        $usersToNotify = $targetUsers->get();

        if ($usersToNotify->isNotEmpty()) {
            Notification::send($usersToNotify, new NewAnnouncementNotification($announcement));
        }
        // -------------------------------------------

        // إعادة التوجيه إلى قائمة الإعلانات مع رسالة نجاح
        return redirect()->route('admin.announcements.index')
                         ->with('success', 'Announcement created successfully.');
    }

    /**
     * Display the specified announcement.
     * يعرض تفاصيل إعلان محدد.
     *
     * @param  \App\Models\Announcement  $announcement  // استخدام Route Model Binding
     */
    public function show(Announcement $announcement)
    {
        // تحميل العلاقات لعرضها في الواجهة
        $announcement->load(['author.user', 'targetClass']);

        // عرض واجهة تفاصيل الإعلان
        return view('web.admin.announcements.show', compact('announcement')); // تأكد من إنشاء هذا الـ view
    }

    /**
     * Show the form for editing the specified announcement.
     * يعرض نموذج تعديل بيانات إعلان موجود.
     *
     * @param  \App\Models\Announcement  $announcement
     */
    public function edit(Announcement $announcement)
    {
        // جلب قائمة الفصول للاختيار
        $classes = KindergartenClass::orderBy('class_name')->pluck('class_name', 'class_id');

        // إرسال بيانات الإعلان الحالي وقائمة الفصول إلى الواجهة
        return view('web.admin.announcements.edit', compact('announcement', 'classes')); // تأكد من إنشاء هذا الـ view
    }

    /**
     * Update the specified announcement in storage.
     * يحدث بيانات الإعلان في قاعدة البيانات.
     *
     * @param  \App\Http\Requests\Admin\UpdateAnnouncementRequest  $request
     * @param  \App\Models\Announcement  $announcement
     */
    public function update(UpdateAnnouncementRequest $request, Announcement $announcement) // استخدام Form Request
    {
        // الحصول على البيانات المتحقق منها
        $validated = $request->validated();

        // تحديث بيانات الإعلان
        $announcement->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'target_class_id' => $validated['target_class_id'] ?? null,
            // يمكن إضافة تحديث لحقول أخرى إذا لزم الأمر (مثل publish_date)
        ]);

        // إعادة التوجيه إلى قائمة الإعلانات مع رسالة نجاح
        return redirect()->route('admin.announcements.index')
                         ->with('success', 'Announcement updated successfully.');
    }

    /**
     * Remove the specified announcement from storage.
     * يحذف الإعلان من قاعدة البيانات.
     *
     * @param  \App\Models\Announcement  $announcement
     */
    public function destroy(Announcement $announcement)
    {
        try {
            // حذف الإعلان
            $announcement->delete();
            // إعادة التوجيه مع رسالة نجاح
            return redirect()->route('admin.announcements.index')
                             ->with('success', 'Announcement deleted successfully.');
        } catch (\Exception $e) {
            // في حال حدوث خطأ (نادر الحدوث هنا)
            return redirect()->route('admin.announcements.index')
                             ->with('error', 'Failed to delete announcement. Please try again.');
        }
    }
}