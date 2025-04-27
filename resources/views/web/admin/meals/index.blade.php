@extends('layouts.admin')

@section('title', 'إدارة الوجبات اليومية')

@section('header-buttons')
<a href="{{ route('admin.meals.create', ['date' => $selectedDate]) }}" class="btn btn-sm btn-success">
    <i data-feather="plus-circle" class="me-1"></i> إضافة وجبة جديدة
</a>
@endsection

@section('content')
<div class="container-fluid">

    {{-- قسم الفلترة --}}
    <div class="card mb-4">
        <div class="card-header">
           <i data-feather="filter" class="me-1"></i> عرض الوجبات حسب
        </div>
        <div class="card-body">
             <form method="GET" action="{{ route('admin.meals.index') }}" class="row g-3 align-items-end">
                {{-- فلتر التاريخ --}}
                <div class="col-md-4">
                    <label for="date" class="form-label">التاريخ:</label>
                    <input type="date" class="form-control form-control-sm" id="date" name="date" value="{{ $selectedDate ?? old('date', now()->format('Y-m-d')) }}" onchange="this.form.submit()"> {{-- تحديث تلقائي عند تغيير التاريخ --}}
                </div>

                {{-- فلتر الفصل --}}
                <div class="col-md-4">
                    <label for="class_id" class="form-label">الفصل الدراسي:</label>
                    <select class="form-select form-select-sm" id="class_id" name="class_id" onchange="this.form.submit()"> {{-- تحديث تلقائي --}}
                        <option value="">-- كل الوجبات (العامة والخاصة) --</option>
                        <option value="general" {{ $selectedClassId === 'general' ? 'selected' : '' }}>الوجبات العامة فقط</option>
                        @foreach($classes as $id => $name)
                            <option value="{{ $id }}" {{ $selectedClassId == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- زر تطبيق الفلتر (اختياري إذا كان التحديث تلقائي) --}}
                {{-- <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">عرض</button>
                </div> --}}
            </form>
        </div>
    </div>

    {{-- جدول عرض الوجبات --}}
    <div class="card">
         <div class="card-header">
            <h5 class="card-title mb-0">وجبات يوم: {{ $selectedDate }}</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>نوع الوجبة</th>
                            <th>الوصف/القائمة</th>
                            <th>الفصل المخصص</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($meals as $index => $meal)
                        <tr>
                            <td>{{ $meals->firstItem() + $index }}</td>
                            <td>
                                @php
                                    $mealTypeClass = match($meal->meal_type) { 'Breakfast' => 'primary', 'Lunch' => 'success', 'Snack' => 'warning', default => 'secondary' };
                                    $mealTypeText = match($meal->meal_type) { 'Breakfast' => 'فطور', 'Lunch' => 'غداء', 'Snack' => 'وجبة خفيفة', default => $meal->meal_type };
                                @endphp
                                <span class="badge bg-{{ $mealTypeClass }}">{{ $mealTypeText }}</span>
                            </td>
                            <td>{{ $meal->menu_description }}</td>
                            <td>
                                @if($meal->kindergartenClass)
                                    <span class="badge bg-info">{{ $meal->kindergartenClass->class_name }}</span>
                                @else
                                    <span class="badge bg-secondary">عامة</span>
                                @endif
                            </td>
                            <td>
                                {{-- <a href="{{ route('admin.meals.show', $meal) }}" class="btn btn-sm btn-info me-1" title="عرض">
                                    <i data-feather="eye"></i>
                                </a> --}}
                                <a href="{{ route('admin.meals.edit', $meal) }}" class="btn btn-sm btn-warning me-1" title="تعديل">
                                    <i data-feather="edit-2"></i>
                                </a>
                                <form action="{{ route('admin.meals.destroy', $meal) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه الوجبة؟');">
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
                            <td colspan="5" class="text-center py-4">لا توجد وجبات مسجلة لهذا اليوم أو الفلترة.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
         @if ($meals->hasPages())
            <div class="card-footer">
                {{ $meals->links() }}
            </div>
        @endif
    </div> {{-- نهاية الـ card --}}
</div>
@endsection