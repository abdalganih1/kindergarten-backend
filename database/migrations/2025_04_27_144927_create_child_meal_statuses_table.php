<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('child_meal_statuses', function (Blueprint $table) {
            $table->id('status_id'); // المفتاح الأساسي للجدول

            // --- المفاتيح الأجنبية ---
            // ربط بجدول الأطفال: لمعرفة الطفل المعني
            $table->foreignId('child_id')
                  ->constrained('children', 'child_id') // الربط بجدول children وعمود child_id
                  ->onDelete('cascade') // عند حذف الطفل، تحذف سجلات حالته
                  ->onUpdate('cascade');

            // ربط بجدول الوجبات اليومية: لمعرفة الوجبة المحددة
            $table->foreignId('meal_id')
                  ->constrained('daily_meals', 'meal_id') // الربط بجدول daily_meals وعمود meal_id
                  ->onDelete('cascade') // عند حذف الوجبة، تحذف سجلات حالتها المرتبطة
                  ->onUpdate('cascade');

            // --- بيانات حالة الوجبة ---
            // حالة تناول الوجبة (مهم جدًا)
            $table->enum('consumption_status', [
                    'EatenWell',      // أكل جيدًا
                    'EatenSome',      // أكل البعض
                    'EatenLittle',    // أكل القليل
                    'NotEaten',       // لم يأكل
                    'Absent',         // كان غائبًا (لتجنب تسجيله كـ لم يأكل)
                    'Refused',        // رفض الأكل
                  ])->default('NotEaten')->comment('حالة تناول الطفل للوجبة');

            // ملاحظات سلوكية أو تفاصيل إضافية (اختياري)
            $table->text('notes')->nullable()->comment('ملاحظات حول سلوك الطفل أثناء الأكل أو تفاصيل أخرى');

            // هوية المستخدم الذي سجل الحالة (مشرف أو معلم - اختياري)
            $table->foreignId('recorded_by_id')
                  ->nullable()
                  ->constrained('users', 'id') // الربط بجدول users
                  ->onDelete('set null') // عند حذف المستخدم، اجعل الحقل null
                  ->onUpdate('cascade');

            // --- التوقيت ---
            $table->timestamps(); // created_at و updated_at

            // --- قيود إضافية ---
            // التأكد من أن كل طفل لديه حالة واحدة فقط لكل وجبة في يوم معين
            $table->unique(['child_id', 'meal_id'], 'child_meal_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('child_meal_statuses');
    }
};
