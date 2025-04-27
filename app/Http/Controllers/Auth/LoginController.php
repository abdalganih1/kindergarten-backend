<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth; // استيراد Auth facade
use Illuminate\Http\Request; // استيراد Request (قد نحتاجه لاحقًا أو في authenticated)

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login based on their role.
     * إعادة تعريف هذه الدالة لتحديد مسار إعادة التوجيه ديناميكيًا.
     *
     * @return string
     */
    protected function redirectTo()
    {
        // الحصول على المستخدم المسجل دخوله
        $user = Auth::user();

        // التحقق من دور المستخدم وتحديد مسار إعادة التوجيه
        switch ($user->role) {
            case 'Admin':
                // المسار المؤدي إلى لوحة تحكم المدير
                // نستخدم اسم المسار (route name) لمرونة أكبر
                return route('admin.dashboard'); // يجب أن يكون اسم المسار 'admin.dashboard' معرفًا في web.php
                // أو يمكنك استخدام المسار المباشر: return '/admin/dashboard';
            case 'Supervisor':
                 // المسار المؤدي إلى لوحة تحكم المشرف
                return route('supervisor.dashboard'); // يجب أن يكون اسم المسار 'supervisor.dashboard' معرفًا في web.php
                // أو: return '/supervisor/dashboard';
            case 'Parent':
                // مسار لولي الأمر (إذا كان لديه واجهة ويب)
                // يمكنك توجيهه إلى صفحة رئيسية أو مسار مخصص له
                // return route('parent.dashboard'); // مثال
                return '/home'; // أو التوجيه إلى المسار الافتراضي /home مؤقتًا
            default:
                // المسار الافتراضي إذا لم يتطابق أي دور (احتياطي)
                return '/home';
        }
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // السماح للضيوف فقط بالوصول إلى نماذج تسجيل الدخول والتسجيل
        // ما عدا دالة تسجيل الخروج
        $this->middleware('guest')->except('logout');
        // السماح للمستخدمين المسجلين فقط بالوصول إلى دالة تسجيل الخروج
        $this->middleware('auth')->only('logout');
    }

     /**
     * Get the needed authorization credentials from the request.
     * التأكد من استخدام 'email' لعملية المصادقة بدلاً من 'username' الافتراضي إذا لزم الأمر.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        // إضافة حقل is_active لشرط تسجيل الدخول (اختياري ولكن موصى به)
        // هذا يمنع المستخدمين غير النشطين من تسجيل الدخول حتى لو كانت كلمة المرور صحيحة
         return array_merge($request->only($this->username(), 'password'), ['is_active' => true]);

        // إذا كنت تستخدم username بدل email:
        // return $request->only($this->username(), 'password');
    }

    /**
     * Get the login username to be used by the controller.
     * تحديد الحقل المستخدم لتسجيل الدخول (email أو username).
     *
     * @return string
     */
    public function username()
    {
        // بناءً على استخدامك لـ 'email' في الـ API Seeder والـ Model،
        // يجب أن نحدد 'email' هنا أيضًا.
        return 'email';
    }

     /**
     * The user has been authenticated.
     * يمكنك أيضًا استخدام هذه الدالة لتنفيذ منطق إضافي بعد المصادقة مباشرةً
     * قبل إعادة التوجيه.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    // protected function authenticated(Request $request, $user)
    // {
    //     // مثال: تسجيل آخر وقت تسجيل دخول
    //     // $user->update(['last_login_at' => now()]);

    //     // لا تقم بإعادة التوجيه من هنا إذا كنت تستخدم redirectTo()
    //     // إعادة التوجيه تتم تلقائيًا بعد تنفيذ هذه الدالة إذا لم تُرجع شيئًا
    // }

}