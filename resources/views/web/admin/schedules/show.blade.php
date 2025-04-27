@extends('layouts.admin')

@section('title', 'تفاصيل نشاط الجدول')

@section('header-buttons')
     <a href="{{ route('admin.schedules.edit', $weeklySchedule) }}" class="btn btn-sm btn-warning me-2">
        <i data-feather="edit-2" class="me-1"></i> تعديل
    </a>
     <form action="{{ route('admin.schedules.destroy', $weeklySchedule) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا النشاط من الجدول؟');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger me-2">
            <i data-feather="trash-2" class="me-1"></i> حذف
        </button>
    </form>
    <a href="{{ route('admin.schedules.index', ['class_id' => $weeklySchedule->class_id, 'day_of_week' => $weeklySchedule->day_of_week]) }}" class="btn btn-sm btn-outline-secondary">
        <i data-feather="arrow-left" class="me-1"></i> العودة إلى الجدول
    </a>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">تفاصيل النشاط في الجدول</h5>
        </div>
        <div class="card-body">
             <dl class="row mb-0">
                 <dt class="col-sm-3">الفصل الدراسي:</dt>
                 <dd class="col-sm-9">{{ $weeklySchedule->kindergartenClass->class_name ?? 'N/A' }}</dd>

                <dt class="col-sm-3">يوم الأسبوع:</dt>
                <dd class="col-sm-9">{{ ['Monday' => 'الإثنين', 'Tuesday' => 'الثلاثاء', 'Wednesday' => 'الأربعاء', 'Thursday' => 'الخميس', 'Friday' => 'الجمعة', 'Saturday' => 'السبت', 'Sunday' => 'الأحد'][$weeklySchedule->day_of_week] ?? $weeklySchedule->day_of_week }}</dd>

                 <dt class="col-sm-3">وقت البدء:</dt>
                 <dd class="col-sm-9">{{ \Carbon\Carbon::parse($weeklySchedule->start_time)->format('h:i A') }}</dd>

                 <dt class="col-sm-3">وقت الانتهاء:</dt>
                 <dd class="col-sm-9">{{ \Carbon\Carbon::parse($weeklySchedule->end_time)->format('h:i A') }}</dd>

                 <dt class="col-sm-3">وصف النشاط:</dt>
                 <dd class="col-sm-9">{{ $weeklySchedule->activity_description }}</dd>

                <dt class="col-sm-3">أنشئ بواسطة:</dt>
                 <dd class="col-sm-9">{{ $weeklySchedule->createdByAdmin->user->name ?? 'N/A' }}</dd>

                 <dt class="col-sm-3">تاريخ الإنشاء:</dt>
                 <dd class="col-sm-9">{{ $weeklySchedule->created_at->format('Y-m-d H:i A') }}</dd>

                 <dt class="col-sm-3">آخر تحديث:</dt>
                 <dd class="col-sm-9">{{ $weeklySchedule->updated_at->format('Y-m-d H:i A') }}</dd>
             </dl>
        </div>
    </div>
</div>
@endsection