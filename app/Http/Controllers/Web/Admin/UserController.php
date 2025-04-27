<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Admin;      // لاستخدامه في إنشاء/تحديث ملف تعريف المدير
use App\Models\ParentModel; // لاستخدامه في إنشاء/تحديث ملف تعريف ولي الأمر
// use App\Models\Supervisor; // إذا كان لديك نموذج للمشرف
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // للتحقق من عدم حذف المستخدم لنفسه
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\DB;   // لاستخدام Transactions
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;

class UserController extends Controller
{
    /**
     * Display a listing of the users with pagination and filtering.
     */
    public function index(Request $request) // <-- إضافة Request
    {
        // --- تنفيذ TODO: Add pagination, Filtering ---
        $query = User::with(['adminProfile', 'parentProfile']); // تحميل ملفات التعريف المرتبطة

        // --- الفلترة ---
        // 1. حسب الدور (Role)
        $role = $request->query('role');
        if ($role && in_array($role, ['Admin', 'Parent', 'Supervisor'])) {
            $query->where('role', $role);
        }

        // 2. حسب الحالة (Active/Inactive)
        $status = $request->query('status');
        if ($status === 'active') {
             $query->where('is_active', true);
        } elseif ($status === 'inactive') {
             $query->where('is_active', false);
        }

        // 3. البحث بالاسم أو البريد الإلكتروني
        $searchTerm = $request->query('search');
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        // --- الترتيب والـ Pagination ---
        $users = $query->latest()->paginate(15)->withQueryString(); // استخدام paginate و withQueryString

        // قائمة الأدوار للفلترة
        $roles = ['Admin' => 'مدير', 'Parent' => 'ولي أمر', 'Supervisor' => 'مشرف'];

        return view('web.admin.users.index', compact('users', 'roles', 'role', 'status', 'searchTerm')); // تمرير متغيرات الفلترة للـ view
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = ['Admin' => 'مدير', 'Parent' => 'ولي أمر', 'Supervisor' => 'مشرف']; // لتحديد الدور في النموذج
        return view('web.admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user and associated profile in storage within a transaction.
     */
    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        // استخدام Transaction لضمان حفظ المستخدم وملفه الشخصي معًا أو لا شيء
        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'is_active' => $validated['is_active'] ?? true, // التأكد من وجود قيمة افتراضية
            ]);

            // --- تنفيذ TODO: إنشاء الملف الشخصي المرتبط ---
            if ($user->role === 'Admin') {
                Admin::create([
                    'user_id' => $user->id,
                    'full_name' => $validated['name'], // يمكن أخذ الاسم من هنا
                    'contact_email' => $validated['admin_contact_email'] ?? $user->email, // حقل إضافي من النموذج؟
                    'contact_phone' => $validated['admin_contact_phone'] ?? null, // حقل إضافي من النموذج؟
                ]);
            } elseif ($user->role === 'Parent') {
                ParentModel::create([
                    'user_id' => $user->id,
                    'full_name' => $validated['name'],
                    'contact_email' => $validated['parent_contact_email'] ?? $user->email, // حقل إضافي؟
                    'contact_phone' => $validated['parent_contact_phone'] ?? null, // حقل إضافي؟
                    'address' => $validated['parent_address'] ?? null, // حقل إضافي؟
                ]);
            }
            // elseif ($user->role === 'Supervisor') {
            //     // إنشاء ملف تعريف للمشرف إذا كان لديك نموذج Supervisor
            //     Supervisor::create(['user_id' => $user->id, /* ... */]);
            // }

            DB::commit(); // تأكيد العملية بنجاح

            return redirect()->route('admin.users.index')->with('success', 'تم إنشاء المستخدم بنجاح.');

        } catch (\Exception $e) {
            DB::rollBack(); // التراجع عن العملية في حالة حدوث خطأ
            \Log::error("User creation failed: " . $e->getMessage()); // تسجيل الخطأ
            return back()->withInput()->with('error', 'فشل إنشاء المستخدم. يرجى المحاولة مرة أخرى.');
        }
    }

    /**
     * Display the specified user and their profile.
     */
    public function show(User $user)
    {
         // تحميل ملفات التعريف والعلاقات الأخرى إذا لزم الأمر (مثل أطفال ولي الأمر)
         $user->load(['adminProfile', 'parentProfile' /*, 'supervisorProfile' */]);
         if ($user->role === 'Parent' && $user->parentProfile) {
             $user->parentProfile->load('children'); // تحميل أطفال ولي الأمر
         }
         // يمكنك تحميل رسائل أو ملاحظات المدير/المشرف هنا أيضًا

         return view('web.admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
         $user->load(['adminProfile', 'parentProfile' /*, 'supervisorProfile' */]);
         $roles = ['Admin' => 'مدير', 'Parent' => 'ولي أمر', 'Supervisor' => 'مشرف'];
         return view('web.admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user and associated profile in storage within a transaction.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        // استخدام Transaction
        DB::beginTransaction();
        try {
            // التعامل مع تحديث كلمة المرور
            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }
            // بيانات المستخدم الأساسية للتحديث
            $userData['name'] = $validated['name'];
            $userData['email'] = $validated['email'];
            $userData['role'] = $validated['role'];
            $userData['is_active'] = $validated['is_active'] ?? $user->is_active; // الحفاظ على القيمة القديمة إذا لم يتم إرسالها

            // تحديث المستخدم
            $user->update($userData);

            // --- تنفيذ TODO: تحديث الملف الشخصي المرتبط ---
            // ملاحظة: قد تحتاج إلى إضافة حقول الملف الشخصي إلى UpdateUserRequest
            if ($user->role === 'Admin' && $user->adminProfile) {
                $user->adminProfile->update([
                    'full_name' => $validated['name'], // تحديث الاسم ليتطابق
                    'contact_email' => $validated['admin_contact_email'] ?? $user->email,
                    'contact_phone' => $validated['admin_contact_phone'] ?? null,
                ]);
            } elseif ($user->role === 'Parent' && $user->parentProfile) {
                 $user->parentProfile->update([
                    'full_name' => $validated['name'],
                    'contact_email' => $validated['parent_contact_email'] ?? $user->email,
                    'contact_phone' => $validated['parent_contact_phone'] ?? null,
                    'address' => $validated['parent_address'] ?? null,
                 ]);
            }
            // elseif ($user->role === 'Supervisor' && $user->supervisorProfile) {
            //     // تحديث ملف المشرف
            // }
             // يمكنك إضافة منطق لإنشاء الملف الشخصي إذا لم يكن موجودًا عند تغيير الدور

            DB::commit();

            return redirect()->route('admin.users.index')->with('success', 'تم تحديث المستخدم بنجاح.');

        } catch (\Exception $e) {
            DB::rollBack();
             \Log::error("User update failed for user {$user->id}: " . $e->getMessage());
            return back()->withInput()->with('error', 'فشل تحديث المستخدم. يرجى المحاولة مرة أخرى.');
        }
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        // --- تنفيذ TODO: التحقق من عدم حذف المستخدم لنفسه ---
        if (Auth::id() === $user->id) {
             return back()->with('error', 'لا يمكنك حذف حسابك الخاص.');
        }

        try {
            // سيتم حذف الملفات الشخصية المرتبطة تلقائيًا إذا تم تعريف
            // ON DELETE CASCADE في المفاتيح الأجنبية بملفات الهجرة.
            $user->delete();
            return redirect()->route('admin.users.index')->with('success', 'تم حذف المستخدم بنجاح.');
        } catch (\Illuminate\Database\QueryException $e) {
            // التعامل مع أخطاء المفتاح الأجنبي (إذا كان المستخدم مرتبطًا بسجلات أخرى لا تحذف تلقائيًا)
             \Log::error("User deletion failed due to FK constraint for user {$user->id}: " . $e->getMessage());
            return back()->with('error', 'لا يمكن حذف المستخدم لوجود سجلات مرتبطة به (مثل إعلانات، فعاليات، إلخ). يرجى التحقق من السجلات المرتبطة.');
        } catch (\Exception $e) {
             \Log::error("User deletion failed for user {$user->id}: " . $e->getMessage());
             return back()->with('error', 'فشل حذف المستخدم. يرجى المحاولة مرة أخرى.');
        }
    }
}