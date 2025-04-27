@extends('layouts.supervisor') {{-- أو layouts.admin --}}

@section('title', 'تفاصيل نشاط الجدول')

@section('header-buttons')
    {{-- لا يوجد أزرار تعديل أو حذف للمشرف --}}
    {{-- استخدام الحل الثاني لرابط العودة (العودة لقائمة الفصل الحالي) --}}
    <a href="{{ route('supervisor.schedules.index', ['class_id' => $weeklySchedule->class_id]) }}" class="btn btn-sm btn-outline-secondary">
        <i data-feather="arrow-left" class="me-1"></i> العودة إلى جدول الفصل
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
                {{-- التأكد من وجود المصفوفة والمفتاح قبل استخدامهما --}}
                <dd class="col-sm-9">{{ isset($daysOfWeek) && isset($daysOfWeek[$weeklySchedule->day_of_week]) ? $daysOfWeek[$weeklySchedule->day_of_week] : $weeklySchedule->day_of_week }}</dd>

                 <dt class="col-sm-3">وقت البدء:</dt>
                 <dd class="col-sm-9">{{ $weeklySchedule->start_time ? \Carbon\Carbon::parse($weeklySchedule->start_time)->format('h:i A') : 'N/A' }}</dd>

                 <dt class="col-sm-3">وقت الانتهاء:</dt>
                 <dd class="col-sm-9">{{ $weeklySchedule->end_time ? \Carbon\Carbon::parse($weeklySchedule->end_time)->format('h:i A') : 'N/A' }}</dd>

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