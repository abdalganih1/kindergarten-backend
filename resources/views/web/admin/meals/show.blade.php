@extends('layouts.admin')

@section('title', 'تفاصيل الوجبة')

@section('header-buttons')
     <a href="{{ route('admin.meals.edit', $meal) }}" class="btn btn-sm btn-warning me-2">
        <i data-feather="edit-2" class="me-1"></i> تعديل
    </a>
     <form action="{{ route('admin.meals.destroy', $meal) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه الوجبة؟');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger me-2">
            <i data-feather="trash-2" class="me-1"></i> حذف
        </button>
    </form>
    <a href="{{ route('admin.meals.index', ['date' => $meal->meal_date]) }}" class="btn btn-sm btn-outline-secondary">
        <i data-feather="arrow-left" class="me-1"></i> العودة لقائمة اليوم
    </a>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">تفاصيل الوجبة</h5>
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">التاريخ:</dt>
                <dd class="col-sm-9">{{ \Carbon\Carbon::parse($meal->meal_date)->format('Y-m-d') }}</dd>

                <dt class="col-sm-3">نوع الوجبة:</dt>
                <dd class="col-sm-9">
                     @php
                        $mealTypeClass = match($meal->meal_type) { 'Breakfast' => 'primary', 'Lunch' => 'success', 'Snack' => 'warning', default => 'secondary' };
                        $mealTypeText = match($meal->meal_type) { 'Breakfast' => 'فطور', 'Lunch' => 'غداء', 'Snack' => 'وجبة خفيفة', default => $meal->meal_type };
                    @endphp
                    <span class="badge bg-{{ $mealTypeClass }} fs-6">{{ $mealTypeText }}</span>
                </dd>

                 <dt class="col-sm-3">الفصل المخصص:</dt>
                <dd class="col-sm-9">
                     @if($meal->kindergartenClass)
                        <span class="badge bg-info fs-6">{{ $meal->kindergartenClass->class_name }}</span>
                    @else
                        <span class="badge bg-secondary fs-6">عامة</span>
                    @endif
                </dd>

                 <dt class="col-sm-3">الوصف / القائمة:</dt>
                <dd class="col-sm-9">{{ $meal->menu_description }}</dd>

                 <dt class="col-sm-3">تاريخ الإنشاء:</dt>
                 <dd class="col-sm-9">{{ $meal->created_at->format('Y-m-d H:i A') }}</dd>

                 <dt class="col-sm-3">آخر تحديث:</dt>
                 <dd class="col-sm-9">{{ $meal->updated_at->format('Y-m-d H:i A') }}</dd>

            </dl>
        </div>
    </div>
</div>
@endsection