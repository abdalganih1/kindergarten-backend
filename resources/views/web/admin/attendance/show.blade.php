@extends('layouts.admin')

@section('title', 'تفاصيل سجل الحضور')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">تفاصيل سجل الحضور</h2>

    <div class="card">
        <div class="card-header">
            سجل ليوم: {{ \Carbon\Carbon::parse($attendance->attendance_date)->format('Y-m-d') }}
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">الطفل:</dt>
                <dd class="col-sm-9">{{ $attendance->child->full_name ?? 'N/A' }}</dd>

                <dt class="col-sm-3">الفصل:</dt>
                <dd class="col-sm-9">{{ $attendance->child->kindergartenClass->class_name ?? 'N/A' }}</dd>

                <dt class="col-sm-3">التاريخ:</dt>
                <dd class="col-sm-9">{{ \Carbon\Carbon::parse($attendance->attendance_date)->format('Y-m-d') }}</dd>

                <dt class="col-sm-3">الحالة:</dt>
                <dd class="col-sm-9">
                     @php
                        $statusClass = '';
                        switch($attendance->status) {
                            case 'Present': $statusClass = 'success'; break;
                            case 'Absent': $statusClass = 'danger'; break;
                            case 'Late': $statusClass = 'warning'; break;
                            case 'Excused': $statusClass = 'info'; break;
                        }
                    @endphp
                    <span class="badge bg-{{ $statusClass }}">{{ $attendance->status }}</span>
                </dd>

                <dt class="col-sm-3">وقت الدخول:</dt>
                <dd class="col-sm-9">{{ $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i A') : '-' }}</dd>

                <dt class="col-sm-3">وقت الخروج:</dt>
                 <dd class="col-sm-9">{{ $attendance->check_out_time ? \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i A') : '-' }}</dd>

                 <dt class="col-sm-3">ملاحظات:</dt>
                <dd class="col-sm-9">{{ $attendance->notes ?: '-' }}</dd>

                <dt class="col-sm-3">سُجّل بواسطة:</dt>
                <dd class="col-sm-9">{{ $attendance->recordedByUser->name ?? 'N/A' }}</dd>

                 <dt class="col-sm-3">وقت التسجيل:</dt>
                 <dd class="col-sm-9">{{ $attendance->created_at->format('Y-m-d H:i A') }}</dd>

                 <dt class="col-sm-3">آخر تحديث:</dt>
                 <dd class="col-sm-9">{{ $attendance->updated_at->format('Y-m-d H:i A') }}</dd>
            </dl>
        </div>
        <div class="card-footer text-end">
             <a href="{{ route('admin.attendance.edit', $attendance->attendance_id) }}" class="btn btn-warning me-2">
                 <i data-feather="edit-2" class="me-1"></i> تعديل
            </a>
            <a href="{{ route('admin.attendance.index', ['date' => $attendance->attendance_date]) }}" class="btn btn-secondary">
                <i data-feather="arrow-left" class="me-1"></i> العودة للقائمة
            </a>
        </div>
    </div>
</div>
@endsection