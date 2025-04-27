@extends('layouts.supervisor') {{-- أو layouts.admin إذا كنت تستخدم نفس الـ layout --}}

@section('title', 'تسجيل حالات الوجبات اليومية')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">تسجيل حالات الوجبات</h1>
        {{-- زر للعودة للداشبورد أو صفحة أخرى مناسبة --}}
        <a href="{{ route('supervisor.dashboard') }}" class="btn btn-outline-secondary btn-sm">
             <i data-feather="arrow-left" class="me-1"></i> العودة للوحة التحكم
        </a>
    </div>

    {{-- 1. نموذج اختيار التاريخ والفصل --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light">
            <i data-feather="filter" class="me-1"></i> اختر التاريخ والفصل لعرض نموذج التسجيل
        </div>
        <div class="card-body">
             {{-- رسالة خطأ إذا لم يكن المشرف مسؤولاً عن أي فصول --}}
             @if(!isset($supervisedClasses) || $supervisedClasses->isEmpty())
                <div class="alert alert-warning" role="alert">
                    لم يتم تعيين أي فصول دراسية لك حتى الآن. لا يمكنك تسجيل حالات الوجبات.
                </div>
             @else
                 <form method="GET" action="{{ route('supervisor.meal_statuses.create') }}" class="row g-3 align-items-center">
                    {{-- اختيار التاريخ --}}
                    <div class="col-md-5">
                        <label for="date" class="form-label visually-hidden">التاريخ:</label>
                        <input type="date" class="form-control" id="date" name="date" value="{{ $selectedDate ?? now()->format('Y-m-d') }}" required>
                    </div>
                    {{-- اختيار الفصل --}}
                    <div class="col-md-5">
                        <label for="class_id" class="form-label visually-hidden">الفصل الدراسي:</label>
                        <select class="form-select" id="class_id" name="class_id" required>
                            <option value="" disabled {{ !$selectedClassId ? 'selected' : '' }}>-- اختر الفصل الدراسي --</option>
                            @foreach($supervisedClasses as $id => $name)
                                <option value="{{ $id }}" {{ $selectedClassId == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- زر العرض --}}
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                           <i data-feather="eye" class="me-1"></i> عرض النموذج
                        </button>
                    </div>
                </form>
             @endif
        </div>
    </div>

    {{-- 2. نموذج تسجيل الحالات (يظهر فقط إذا تم اختيار فصل ووجد وجبات وأطفال) --}}
    @if(isset($selectedClassId) && isset($mealsForDay) && isset($childrenWithMeals) && !$mealsForDay->isEmpty() && !$childrenWithMeals->isEmpty())
        <form action="{{ route('supervisor.meal_statuses.store') }}" method="POST">
            @csrf
            <input type="hidden" name="date" value="{{ $selectedDate }}">
            <input type="hidden" name="class_id" value="{{ $selectedClassId }}">

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                     <h5 class="card-title mb-0">
                        <i data-feather="edit" class="me-1"></i>
                        تسجيل حالات وجبات فصل: <span class="fw-bold">{{ $supervisedClasses[$selectedClassId] ?? 'N/A' }}</span>
                        - ليوم: <span class="fw-bold">{{ $selectedDate }}</span>
                    </h5>
                </div>
                 <div class="card-body p-0"> {{-- استخدام p-0 لإزالة الحشو --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0" style="min-width: 700px;"> {{-- عرض أفضل على الشاشات الأوسع --}}
                            <thead class="table-light text-center">
                                <tr>
                                    {{-- تثبيت عمود اسم الطفل --}}
                                    <th rowspan="2" class="align-middle position-sticky start-0 bg-light" style="min-width: 150px; z-index: 2;">اسم الطفل</th>
                                    {{-- عرض أعمدة الوجبات لهذا اليوم --}}
                                    @foreach($mealsForDay as $meal)
                                        <th colspan="2" class="text-nowrap">
                                             {{ match($meal->meal_type) { 'Breakfast' => 'فطور', 'Lunch' => 'غداء', 'Snack' => 'وجبة خفيفة', default => $meal->meal_type } }}
                                            <small class="d-block text-muted">({{ Str::limit($meal->menu_description, 25) }})</small>
                                        </th>
                                    @endforeach
                                </tr>
                                <tr>
                                     @foreach($mealsForDay as $meal)
                                        <th>الحالة <span class="text-danger">*</span></th>
                                        <th>ملاحظات</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($childrenWithMeals as $child)
                                    <tr>
                                        {{-- تثبيت عمود اسم الطفل --}}
                                        <td class="fw-bold position-sticky start-0 bg-white" style="z-index: 1;">{{ $child->full_name }}</td>
                                        @foreach($mealsForDay as $meal)
                                            @php
                                                // البحث عن الحالة المسجلة حاليًا لهذا الطفل وهذه الوجبة
                                                $currentStatus = $child->mealStatuses->firstWhere('meal_id', $meal->meal_id);
                                                $statusValue = $currentStatus->consumption_status ?? 'NotEaten'; // القيمة الافتراضية
                                                $notesValue = $currentStatus->notes ?? '';
                                                $inputBaseName = "statuses[{$child->child_id}][{$meal->meal_id}]";
                                                // التحقق من حالة الحضور (يتطلب تحميل علاقة الحضور)
                                                // $isAbsent = $child->attendances->firstWhere('attendance_date', $selectedDate)?->status === 'Absent'; // تفعيل إذا لزم الأمر
                                                 $isAbsent = false; // تعطيل مؤقتًا لعدم تعقيد الاستعلام
                                            @endphp
                                            {{-- عمود اختيار الحالة --}}
                                            <td>
                                                <select class="form-select form-select-sm @error($inputBaseName.'.status') is-invalid @enderror" name="{{ $inputBaseName }}[status]" required {{ $isAbsent ? 'disabled' : '' }} title="{{ $isAbsent ? 'الطفل غائب اليوم' : '' }}">
                                                    @foreach($consumptionStatuses as $value => $label)
                                                         @php
                                                             $selected = (old($inputBaseName.'.status', $isAbsent ? 'Absent' : $statusValue) == $value);
                                                             $disabled = $isAbsent && $value !== 'Absent'; // تعطيل الخيارات الأخرى إذا كان غائبًا
                                                         @endphp
                                                        <option value="{{ $value }}" {{ $selected ? 'selected' : '' }} {{ $disabled ? 'disabled' : '' }}>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error($inputBaseName.'.status') <div class="invalid-feedback small d-block">{{ $message }}</div> @enderror
                                            </td>
                                            {{-- عمود الملاحظات --}}
                                            <td>
                                                <input type="text" class="form-control form-control-sm @error($inputBaseName.'.notes') is-invalid @enderror" name="{{ $inputBaseName }}[notes]" value="{{ old($inputBaseName.'.notes', $notesValue) }}" placeholder="اختياري..." {{ $isAbsent ? 'disabled' : '' }}>
                                                 @error($inputBaseName.'.notes') <div class="invalid-feedback small d-block">{{ $message }}</div> @enderror
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                 </div>
                 <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary btn-lg px-4">
                         <i data-feather="save" class="me-1"></i> حفظ جميع الحالات
                    </button>
                 </div>
            </div> {{-- نهاية الـ card --}}
        </form>
    @elseif(isset($selectedClassId) && $mealsForDay->isEmpty())
        <div class="alert alert-info text-center mt-4">
            <i data-feather="info" class="me-1"></i> لا توجد وجبات مسجلة لهذا اليوم ({{ $selectedDate }}) للفصل المحدد. لا يمكن تسجيل الحالات.
        </div>
    @elseif(isset($selectedClassId) && $childrenWithMeals->isEmpty())
         <div class="alert alert-info text-center mt-4">
             <i data-feather="info" class="me-1"></i> لا يوجد أطفال في الفصل المحدد ({{ $supervisedClasses[$selectedClassId] ?? '' }}). لا يمكن تسجيل الحالات.
        </div>
    @endif

</div>
@endsection

@push('styles')
<style>
    /* لضمان عمل تثبيت العمود الأول بشكل جيد */
    .table-responsive {
      overflow-x: auto;
    }
    .table th.position-sticky,
    .table td.position-sticky {
      z-index: 1; /* ليكون فوق الصفوف الأخرى */
    }
    .table thead th.position-sticky {
      z-index: 3; /* ليكون فوق كل شيء */
    }
</style>
@endpush