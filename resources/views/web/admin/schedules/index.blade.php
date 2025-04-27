@extends('layouts.admin')

@section('title', 'إدارة الجداول الأسبوعية')

@section('header-buttons')
<a href="{{ route('admin.schedules.create', request()->only(['class_id'])) }}" class="btn btn-sm btn-success">
    <i data-feather="plus-circle" class="me-1"></i> إضافة نشاط للجدول
</a>
@endsection

@section('content')
<div class="container-fluid">

    {{-- قسم الفلترة --}}
    <div class="card mb-4">
        <div class="card-header">
           <i data-feather="filter" class="me-1"></i> عرض الجدول حسب
        </div>
        <div class="card-body">
             <form method="GET" action="{{ route('admin.schedules.index') }}" class="row g-3 align-items-end">
                {{-- فلتر الفصل --}}
                <div class="col-md-5">
                    <label for="class_id" class="form-label">الفصل الدراسي:</label>
                    <select class="form-select form-select-sm" id="class_id" name="class_id" onchange="this.form.submit()">
                        <option value="">-- اختر فصل لعرض جدوله --</option>
                        {{-- التأكد من أن $classes كائن يمكن التكرار عليه --}}
                        @if(isset($classes) && !$classes->isEmpty())
                            @foreach($classes as $id => $name)
                                <option value="{{ $id }}" {{ $classId == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                 {{-- فلتر اليوم (يظهر فقط إذا تم اختيار فصل) --}}
                <div class="col-md-5" id="day-filter-group" style="{{ $classId ? '' : 'display: none;' }}">
                    <label for="day_of_week" class="form-label">يوم الأسبوع:</label>
                    <select class="form-select form-select-sm" id="day_of_week" name="day_of_week" onchange="this.form.submit()">
                        <option value="">-- كل الأيام --</option>
                         {{-- التأكد من أن $daysOfWeek مصفوفة --}}
                         @if(isset($daysOfWeek) && is_array($daysOfWeek))
                            @foreach($daysOfWeek as $value => $label)
                                <option value="{{ $value }}" {{ $dayOfWeek == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                 {{-- زر إعادة التعيين يظهر إذا كان هناك فلتر مطبق --}}
                 @if($classId || $dayOfWeek)
                 <div class="col-md-2">
                     <a href="{{ route('admin.schedules.index') }}" class="btn btn-secondary btn-sm w-100">إعادة التعيين</a>
                 </div>
                 @endif

            </form>
        </div>
    </div>


    {{-- جدول عرض الجدول الأسبوعي --}}
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                الجدول الأسبوعي
                {{-- التحقق من وجود الفصل وعرض اسمه --}}
                @if(isset($classId) && isset($classes) && $classes->has($classId))
                 - {{ $classes->get($classId) }}
                @endif

                {{-- التحقق من وجود اليوم وعرض اسمه --}}
                @if(isset($dayOfWeek) && !empty($dayOfWeek) && isset($daysOfWeek) && isset($daysOfWeek[$dayOfWeek]))
                 - {{ $daysOfWeek[$dayOfWeek] }}
                @endif
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            {{-- إظهار/إخفاء عمود الفصل --}}
                             @if(!isset($classId) || empty($classId))
                                <th>الفصل</th>
                            @endif
                            <th>اليوم</th>
                            <th>وقت البدء</th>
                            <th>وقت الانتهاء</th>
                            <th>النشاط</th>
                            <th>أنشئ بواسطة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($paginatedSchedules as $index => $schedule)
                        <tr>
                            <td>{{ $paginatedSchedules->firstItem() + $index }}</td>
                             {{-- عرض اسم الفصل فقط إذا لم نقم بالفلترة حسب فصل معين --}}
                             @if(!isset($classId) || empty($classId))
                                <td>{{ $schedule->kindergartenClass->class_name ?? 'N/A' }}</td>
                            @endif
                            {{-- عرض اسم اليوم (تحقق إضافي لـ day_of_week) --}}
                             <td>{{ isset($daysOfWeek) && isset($daysOfWeek[$schedule->day_of_week]) ? $daysOfWeek[$schedule->day_of_week] : $schedule->day_of_week }}</td>
                            <td>{{ $schedule->start_time ? \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') : 'N/A' }}</td>
                            <td>{{ $schedule->end_time ? \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') : 'N/A' }}</td>
                            <td>{{ $schedule->activity_description }}</td>
                            <td>{{ $schedule->createdByAdmin->user->name ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('admin.schedules.edit', $schedule) }}" class="btn btn-sm btn-warning me-1" title="تعديل">
                                    <i data-feather="edit-2"></i>
                                </a>
                                <form action="{{ route('admin.schedules.destroy', $schedule) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا النشاط من الجدول؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                        <i data-feather="trash-2"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            {{-- تعديل colspan بناءً على عرض عمود الفصل --}}
                            <td colspan="{{ (!isset($classId) || empty($classId)) ? 8 : 7 }}" class="text-center py-4">
                                @if(isset($classId) && !empty($classId))
                                    لا توجد أنشطة مجدولة لهذا الفصل
                                    {{-- عرض اليوم فقط إذا كان موجودًا وصالحًا --}}
                                    @if(isset($dayOfWeek) && !empty($dayOfWeek) && isset($daysOfWeek) && isset($daysOfWeek[$dayOfWeek]))
                                         في يوم {{ $daysOfWeek[$dayOfWeek] }}
                                    @endif
                                    .
                                    <a href="{{ route('admin.schedules.create', ['class_id' => $classId]) }}">أضف نشاطًا الآن؟</a>
                                @else
                                    الرجاء اختيار فصل دراسي لعرض جدوله.
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
         @if ($paginatedSchedules->hasPages())
            <div class="card-footer">
                {{ $paginatedSchedules->links() }}
            </div>
        @endif
    </div> {{-- نهاية الـ card --}}
</div>
@endsection

@push('scripts')
<script>
    // إظهار/إخفاء فلتر اليوم بناءً على اختيار الفصل
    const classSelect = document.getElementById('class_id');
    const dayFilterGroup = document.getElementById('day-filter-group');

    function toggleDayFilter() {
         if (classSelect && dayFilterGroup) { // التأكد من وجود العناصر
             if (classSelect.value) {
                dayFilterGroup.style.display = 'block';
            } else {
                dayFilterGroup.style.display = 'none';
            }
         }
    }

    if(classSelect){
        classSelect.addEventListener('change', toggleDayFilter);
    }
    // التشغيل الأولي عند تحميل الصفحة
     document.addEventListener('DOMContentLoaded', toggleDayFilter);
</script>
@endpush