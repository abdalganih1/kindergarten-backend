<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // استيراد Auth facade
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string ...$roles  // استقبال الأدوار المسموح بها كمعاملات متعددة
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. تحقق مما إذا كان المستخدم مسجل الدخول
        if (!Auth::check()) {
            // إذا كان الطلب يتوقع JSON (API)، أرجع خطأ JSON
            if ($request->expectsJson()) {
                 return response()->json(['error' => 'Unauthenticated.'], 401);
            }
            // للطلبات العادية (Web)، أعد التوجيه إلى صفحة تسجيل الدخول
            return redirect('login');
        }

        // 2. احصل على المستخدم المسجل دخوله
        $user = Auth::user();

        // 3. تحقق مما إذا كان دور المستخدم ضمن الأدوار المسموح بها
        foreach ($roles as $role) {
            // نفترض أن لديك عمود 'role' في جدول users
            if ($user->role == $role) {
                // إذا تطابق الدور، اسمح للطلب بالمرور
                return $next($request);
            }
        }

        // 4. إذا لم يتطابق أي دور، أرجع خطأ "غير مصرح به"
        if ($request->expectsJson()) {
            // لـ API
            return response()->json(['error' => 'Unauthorized. Access Denied.'], 403);
        } else {
            // لـ Web (يمكنك إعادة التوجيه إلى صفحة خطأ مخصصة)
            abort(403, 'Unauthorized action.');
        }
    }
}