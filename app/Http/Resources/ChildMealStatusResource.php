<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChildMealStatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // ترجمة حالة التناول إلى نص مفهوم
        $statusText = match ($this->consumption_status) {
            'EatenWell' => 'أكل جيدًا',
            'EatenSome' => 'أكل البعض',
            'EatenLittle' => 'أكل القليل',
            'NotEaten' => 'لم يأكل',
            'Refused' => 'رفض الأكل',
            'Absent' => 'غائب',
            default => $this->consumption_status // قيمة افتراضية إذا أضيف شيء جديد
        };

        return [
            'status_id' => $this->status_id,
            'consumption_status' => $this->consumption_status, // القيمة الأصلية (enum)
            'status_text' => $statusText, // النص المترجم
            'notes' => $this->notes,
            'recorded_at' => $this->updated_at->format('Y-m-d H:i'), // وقت آخر تحديث للحالة

            // تضمين معلومات الوجبة (إذا تم تحميلها)
            'meal' => new DailyMealResource($this->whenLoaded('dailyMeal')),

            // تضمين معلومات الطفل (إذا تم تحميلها) - قد لا نحتاجه إذا كان الفلتر حسب الطفل
            'child' => new ChildResource($this->whenLoaded('child')),

            // تضمين معلومات المسجل (إذا تم تحميلها) - قد لا تكون مهمة لولي الأمر
            // 'recorded_by' => new UserResource($this->whenLoaded('recordedBy')),
        ];
    }
}