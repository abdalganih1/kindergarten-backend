<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule; // استيراد Rule

class StoreDailyMealRequest extends FormRequest
{
    public function authorize(): bool { return Auth::check() && Auth::user()->role === 'Admin'; }
    public function rules(): array
    {
        return [
            'meal_date' => ['required', 'date_format:Y-m-d'],
            'meal_type' => ['required', 'string', Rule::in(['Breakfast', 'Lunch', 'Snack'])],
            'menu_description' => ['required', 'string', 'max:5000'],
            'class_id' => ['nullable', 'integer', 'exists:kindergarten_classes,class_id'],
        ];
    }
}