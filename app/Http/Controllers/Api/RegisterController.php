<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\RegisterParentRequest; // استيراد Form Request
use App\Models\User;
use App\Models\ParentModel; // استيراد نموذج ولي الأمر
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;   // لاستخدام Transactions
use App\Http\Resources\UserResource; // لإرجاع بيانات المستخدم

class RegisterController extends Controller
{
    /**
     * Handle a registration request for a new parent user.
     *
     * @param  \App\Http\Requests\Api\RegisterParentRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterParentRequest $request)
    {
        // الحصول على البيانات المتحقق منها
        $validated = $request->validated();

        // استخدام Transaction لضمان إنشاء المستخدم والملف الشخصي معًا
        DB::beginTransaction();
        try {
            // 1. إنشاء سجل المستخدم (User)
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'Parent', // تعيين الدور كـ Parent
                'is_active' => true, // يمكن جعل الحساب غير نشط ويتطلب موافقة المدير
            ]);

            // 2. إنشاء سجل ولي الأمر (ParentModel) المرتبط
            $parentProfile = ParentModel::create([
                'user_id' => $user->id,
                'full_name' => $validated['name'], // استخدام نفس الاسم مبدئيًا
                'contact_email' => $validated['contact_email'] ?? null, // بريد الاتصال الاختياري
                'contact_phone' => $validated['contact_phone'] ?? null, // الهاتف الاختياري
                'address' => $validated['address'] ?? null,       // العنوان الاختياري
            ]);

            DB::commit(); // تأكيد العملية

            // --- خيارات الرد ---
            // الخيار أ: إرجاع بيانات المستخدم الجديد فقط (بدون تسجيل دخول تلقائي)
            // return response()->json([
            //     'message' => 'Parent account created successfully. Please login.',
            //     'user' => new UserResource($user->load('parentProfile'))
            // ], 201);

            // الخيار ب: إرجاع بيانات المستخدم مع توكن تسجيل الدخول (تسجيل دخول تلقائي)
            // يجب تحديد اسم للجهاز للتوكن
             $deviceName = $request->input('device_name', 'Parent Registration'); // استخدام اسم افتراضي أو أخذه من الطلب
             $token = $user->createToken($deviceName)->plainTextToken;

            return response()->json([
                'message' => 'Parent account created and logged in successfully.',
                'token' => $token,
                'user' => new UserResource($user->load('parentProfile')) // إرجاع بيانات المستخدم وملفه الشخصي
            ], 201); // 201 Created status

        } catch (\Exception $e) {
            DB::rollBack(); // التراجع في حالة الخطأ
            \Log::error("Parent registration failed: " . $e->getMessage());
            return response()->json([
                'message' => 'Registration failed. Please try again later.',
                'error' => $e->getMessage() // (للتصحيح فقط، قم بإزالته في الإنتاج)
            ], 500); // Internal Server Error
        }
    }
}