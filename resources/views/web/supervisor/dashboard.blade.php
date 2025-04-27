@extends('layouts.supervisor') {{-- أو layouts.admin إذا كنت تستخدم نفس الـ layout --}}

@section('title', 'لوحة تحكم المشرف')

@section('content')
<div class="container-fluid">

    {{-- رسالة إذا لم يكن المشرف مسؤولاً عن أي فصول --}}
    @if(isset($noClassesAssigned) && $noClassesAssigned)
        <div class="alert alert-warning mt-4" role="alert">
           <i data-feather="alert-circle" class="me-1 align-text-bottom"></i>
           لم يتم تعيين أي فصول دراسية لك حتى الآن. يرجى التواصل مع الإدارة.
        </div>
    @else {{-- عرض لوحة التحكم فقط إذا كان هناك فصول --}}

        {{-- قسم الإحصائيات السريعة --}}
        <div class="row g-4 mb-4">
            {{-- إجمالي أطفال المشرف --}}
            <div class="col-lg-4 col-md-6">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-4 fw-bold">{{ $stats['total_supervised_children'] ?? 0 }}</div>
                            <div>إجمالي الأطفال (فصولي)</div>
                        </div>
                        <i data-feather="users" class="opacity-50" style="font-size: 2.5rem;"></i>
                    </div>
                     <div class="card-footer d-flex align-items-center justify-content-between small">
                        <a class="text-white stretched-link" href="{{ route('supervisor.children.index') }}">عرض الأطفال</a>
                        <div class="text-white"><i data-feather="chevron-left"></i></div>
                    </div>
                </div>
            </div>

            {{-- إجمالي فصول المشرف --}}
            <div class="col-lg-4 col-md-6">
                <div class="card bg-info text-white h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-4 fw-bold">{{ $stats['total_supervised_classes'] ?? 0 }}</div>
                            <div>الفصول التي أشرف عليها</div>
                        </div>
                        <i data-feather="layers" class="opacity-50" style="font-size: 2.5rem;"></i>
                    </div>
                     {{-- لا يوجد رابط تفاصيل مناسب هنا عادة --}}
                </div>
            </div>

            {{-- نسبة الحضور اليوم --}}
            <div class="col-lg-4 col-md-6">
                <div class="card bg-success text-white h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                         <div>
                            <div class="fs-4 fw-bold">{{ $attendanceToday['present_percentage'] ?? 0 }}%</div>
                            <div>نسبة الحضور اليوم</div>
                        </div>
                        <i data-feather="check-circle" class="opacity-50" style="font-size: 2.5rem;"></i>
                     </div>
                     <div class="card-footer d-flex align-items-center justify-content-between small">
                        <a class="text-white stretched-link" href="{{ route('supervisor.attendance.index') }}">سجل الحضور</a>
                        <div class="text-white"><i data-feather="chevron-left"></i></div>
                    </div>
                </div>
            </div>
        </div>

         {{-- تفاصيل الحضور لليوم وتنبيه التسجيل --}}
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                       <i data-feather="check-square" class="me-1"></i> متابعة الحضور لليوم ({{ now()->format('Y-m-d') }})
                        <a href="{{ route('supervisor.attendance.index', ['date' => now()->format('Y-m-d')]) }}" class="float-end btn btn-sm btn-outline-primary">عرض سجل اليوم</a>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col"><div class="fs-5 fw-bold text-success">{{ $attendanceToday['present'] ?? 0 }}</div><div class="text-muted small">حاضر</div></div>
                            <div class="col"><div class="fs-5 fw-bold text-danger">{{ $attendanceToday['absent'] ?? 0 }}</div><div class="text-muted small">غائب</div></div>
                            <div class="col"><div class="fs-5 fw-bold text-warning">{{ $attendanceToday['late'] ?? 0 }}</div><div class="text-muted small">متأخر</div></div>
                            <div class="col"><div class="fs-5 fw-bold">{{ $attendanceToday['total_recorded'] ?? 0 }} / {{ $attendanceToday['total_supervised'] ?? 0 }}</div><div class="text-muted small">إجمالي المسجلين</div></div>
                        </div>
                         @if($attendanceToday['total_recorded'] < $attendanceToday['total_supervised'])
                        <hr>
                        <h6 class="text-danger"><i data-feather="alert-triangle" class="me-1"></i> أطفال لم يتم تسجيل حضورهم اليوم:</h6>
                            <ul class="list-inline small">
                                @foreach($recentActivities['missing_attendance_children'] as $child)
                                    <li class="list-inline-item border rounded px-2 py-1 mb-1">
                                        <a href="{{ route('supervisor.children.show', $child) }}">{{ $child->full_name }}</a>
                                    </li>
                                @endforeach
                                @if(count($recentActivities['missing_attendance_children']) >= 10)
                                    <li class="list-inline-item">...</li>
                                @endif
                            </ul>
                            <button type="button" class="btn btn-sm btn-success mt-2" data-bs-toggle="modal" data-bs-target="#batchAttendanceModalSupervisor">
                                <i data-feather="plus-circle" class="me-1"></i> تسجيل الحضور الآن
                            </button>
                        @else
                             <div class="alert alert-success small mt-3 mb-0" role="alert">
                                <i data-feather="check" class="me-1 align-text-bottom"></i>
                                تم تسجيل الحضور لجميع الأطفال في الفصول التي تشرف عليها لهذا اليوم.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>


        {{-- قسم الأنشطة الأخيرة (ملاحظات، رسائل، فعاليات) --}}
        <div class="row g-4">
            {{-- أحدث الملاحظات --}}
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header"><i data-feather="eye" class="me-1"></i> أحدث الملاحظات الواردة</div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                             @forelse ($recentActivities['latest_observations'] as $observation)
                                <li class="list-group-item">
                                    <p class="mb-1 small">{{ Str::limit($observation->observation_text, 60) }}</p>
                                    <small class="text-muted">
                                        <i data-feather="user" class="feather-xs"></i> {{ $observation->parentSubmitter->user->name ?? 'N/A' }}
                                        @if($observation->child)
                                         (عن: <a href="{{ route('supervisor.children.show', $observation->child) }}">{{ $observation->child->first_name }}</a>)
                                        @endif
                                        - {{ $observation->submitted_at->diffForHumans() }}
                                    </small>
                                </li>
                             @empty
                                 <li class="list-group-item text-muted text-center">لا توجد ملاحظات جديدة.</li>
                             @endforelse
                        </ul>
                    </div>
                    {{-- <div class="card-footer text-center">
                        <a href="#" class="small">عرض كل الملاحظات</a>
                    </div> --}}
                </div>
            </div>

            {{-- أحدث الرسائل الواردة --}}
            <div class="col-lg-4">
                 <div class="card h-100">
                    <div class="card-header"><i data-feather="inbox" class="me-1"></i> أحدث الرسائل الواردة</div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                             @forelse ($recentActivities['latest_messages'] as $message)
                                <li class="list-group-item {{ is_null($message->read_at) ? 'list-group-item-warning' : '' }}"> {{-- تمييز غير المقروء --}}
                                    <a href="{{ route('supervisor.messages.show', $message) }}" class="fw-bold">
                                        {{ $message->subject ?: Str::limit($message->body, 30) }}
                                    </a>
                                    <small class="d-block text-muted">
                                        <i data-feather="user" class="feather-xs"></i> من: {{ $message->sender->name ?? 'N/A' }}
                                        - {{ $message->sent_at->diffForHumans() }}
                                    </small>
                                </li>
                             @empty
                                 <li class="list-group-item text-muted text-center">لا توجد رسائل واردة جديدة.</li>
                             @endforelse
                        </ul>
                    </div>
                     <div class="card-footer text-center">
                        <a href="{{ route('supervisor.messages.index') }}" class="small">عرض كل الرسائل</a>
                    </div>
                </div>
            </div>

             {{-- الفعاليات القادمة --}}
            <div class="col-lg-4">
                 <div class="card h-100">
                    <div class="card-header"><i data-feather="calendar" class="me-1"></i> الفعاليات القادمة</div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                             @forelse ($recentActivities['upcoming_events'] as $event)
                                <li class="list-group-item">
                                    {{-- المشرف قد لا يكون لديه صفحة عرض فعالية، يمكن عرض التفاصيل هنا --}}
                                    <strong>{{ $event->event_name }}</strong>
                                    <small class="d-block text-muted">
                                         التاريخ: {{ \Carbon\Carbon::parse($event->event_date)->format('Y-m-d H:i') }}
                                        ({{ \Carbon\Carbon::parse($event->event_date)->diffForHumans() }})
                                        @if($event->location) | الموقع: {{ $event->location }} @endif
                                    </small>
                                </li>
                             @empty
                                  <li class="list-group-item text-muted text-center">لا توجد فعاليات قادمة.</li>
                             @endforelse
                        </ul>
                    </div>
                    {{-- <div class="card-footer text-center">
                        <a href="#" class="small">عرض كل الفعاليات</a>
                    </div> --}}
                </div>
            </div>
        </div>

        {{-- Modal تسجيل الحضور (نفس الموجود في attendance.index) --}}
        @include('web.supervisor.attendance._batch_modal') {{-- تضمين الـ modal من ملف جزئي --}}

    @endif {{-- نهاية التحقق من وجود فصول للمشرف --}}
</div>
@endsection

@push('scripts')
{{-- قد تحتاج لسكريبتات إضافية هنا إذا كان لديك رسوم بيانية أو تفاعلات أخرى --}}
@endpush