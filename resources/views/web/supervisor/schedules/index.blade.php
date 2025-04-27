@extends('layouts.supervisor') {{-- أو layouts.supervisor --}}

@section('title', 'إدارة الجداول الأسبوعية (فصولي)')

@section('header-buttons')
{{-- زر إضافة نشاط يشير لمسار المشرف --}}

@endsection

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">الجداول الأسبوعية للفصول التي تشرف عليها</h2>

     @if(isset($noClassesAssigned) && $noClassesAssigned)
        <div class="alert alert-warning" role="alert">
           <i data-feather="alert-circle" class="me-1 align-text-bottom"></i>
           لم يتم تعيين أي فصول دراسية لك حتى الآن.
        </div>
    @else
        {{-- قسم الفلترة (يعرض فصول المشرف فقط) --}}
        <div class="card mb-4">
            <div class="card-header">
               <i data-feather="filter" class="me-1"></i> عرض الجدول حسب
            </div>
            <div class="card-body">
                 <form method="GET" action="{{ route('supervisor.schedules.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label for="class_id" class="form-label">الفصل الدراسي:</label>
                        <select class="form-select form-select-sm" id="class_id" name="class_id" onchange="this.form.submit()">
                            <option value="">-- اختر فصل لعرض جدوله --</option>
                            {{-- التأكد من أن $supervisedClasses مجموعة يمكن التكرار عليها --}}
                            @if(isset($supervisedClasses) && !$supervisedClasses->isEmpty())
                                @foreach($supervisedClasses as $id => $name)
                                    <option value="{{ $id }}" {{ $classId == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-5" id="day-filter-group" style="{{ $classId ? '' : 'display: none;' }}">
                        <label for="day_of_week" class="form-label">يوم الأسبوع:</label>
                        <select class="form-select form-select-sm" id="day_of_week" name="day_of_week" onchange="this.form.submit()">
                            <option value="">-- كل الأيام --</option>
                             @if(isset($daysOfWeek) && is_array($daysOfWeek))
                                @foreach($daysOfWeek as $value => $label)
                                    <option value="{{ $value }}" {{ $dayOfWeek == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                     @if($classId || $dayOfWeek)
                     <div class="col-md-2">
                         <a href="{{ route('supervisor.schedules.index') }}" class="btn btn-secondary btn-sm w-100">إعادة التعيين</a>
                     </div>
                     @endif
                </form>
            </div>
        </div>

        {{-- جدول عرض الجدول الأسبوعي --}}
                {{-- جدول عرض الجدول الأسبوعي --}}
                <div class="card">
            {{-- ... الهيدر كما هو ... --}}
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                @if(!isset($classId) || empty($classId))<th>الفصل</th>@endif
                                <th>اليوم</th>
                                <th>وقت البدء</th>
                                <th>وقت الانتهاء</th>
                                <th>النشاط</th>
                                <th>أنشئ بواسطة</th>
                                {{-- === تعديل: إزالة أو تعديل عمود الإجراءات === --}}
                                <th>عرض</th> {{-- تغيير اسم العمود --}}
                                {{-- <th>الإجراءات</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($paginatedSchedules as $index => $schedule)
                            <tr>
                                <td>{{ $paginatedSchedules->firstItem() + $index }}</td>
                                @if(!isset($classId) || empty($classId))<td>{{ $schedule->kindergartenClass->class_name ?? 'N/A' }}</td>@endif
                                <td>{{ isset($daysOfWeek[$schedule->day_of_week]) ? $daysOfWeek[$schedule->day_of_week] : $schedule->day_of_week }}</td>
                                <td>{{ $schedule->start_time ? \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') : 'N/A' }}</td>
                                <td>{{ $schedule->end_time ? \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') : 'N/A' }}</td>
                                <td>{{ $schedule->activity_description }}</td>
                                <td>{{ $schedule->createdByAdmin->user->name ?? 'N/A' }}</td>
                                {{-- === تعديل: إزالة أزرار التعديل والحذف === --}}
                                <td>
                                    <a href="{{ route('supervisor.schedules.show', $schedule) }}" class="btn btn-sm btn-info" title="عرض التفاصيل">
                                        <i data-feather="eye"></i>
                                    </a>
                                </td>
                                {{-- === نهاية التعديل === --}}
                            </tr>
                            @empty
                            <tr>
                                {{-- تعديل colspan --}}
                                <td colspan="{{ (!isset($classId) || empty($classId)) ? 8 : 7 }}" class="text-center py-4">
                                     @if(isset($classId) && !empty($classId))
                                        لا توجد أنشطة مجدولة لهذا الفصل
                                        @if(isset($dayOfWeek) && !empty($dayOfWeek) && isset($daysOfWeek[$dayOfWeek]))
                                             في يوم {{ $daysOfWeek[$dayOfWeek] }}
                                        @endif.
                                        {{-- لا نعرض رابط إضافة للمشرف هنا --}}
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
    @endif {{-- نهاية التحقق من وجود فصول للمشرف --}}
</div>
@endsection

@push('scripts')
{{-- نفس سكريبت إظهار/إخفاء فلتر اليوم --}}
<script>
    const classSelect = document.getElementById('class_id');
    const dayFilterGroup = document.getElementById('day-filter-group');
    function toggleDayFilter() { if (classSelect && dayFilterGroup) { dayFilterGroup.style.display = classSelect.value ? 'block' : 'none'; } }
    if(classSelect){ classSelect.addEventListener('change', toggleDayFilter); }
    document.addEventListener('DOMContentLoaded', toggleDayFilter);
</script>
@endpush