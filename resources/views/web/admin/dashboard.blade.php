@extends('layouts.admin') {{-- استخدام الـ layout الأساسي --}}

@section('title', 'لوحة التحكم الرئيسية') {{-- عنوان الصفحة --}}

@section('content')
<div class="container-fluid">

    {{-- قسم الإحصائيات السريعة (Cards) --}}
    <div class="row g-4 mb-4">
        {{-- إجمالي الأطفال --}}
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fs-4 fw-bold">{{ $stats['total_children'] ?? 0 }}</div>
                        <div>إجمالي الأطفال</div>
                    </div>
                    <i data-feather="users" class="opacity-50" style="font-size: 2.5rem;"></i>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between small">
                    <a class="text-white stretched-link" href="{{ route('admin.children.index') }}">عرض التفاصيل</a>
                    <div class="text-white"><i data-feather="chevron-left"></i></div>
                </div>
            </div>
        </div>

        {{-- إجمالي أولياء الأمور --}}
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white h-100">
                 <div class="card-body d-flex justify-content-between align-items-center">
                     <div>
                        <div class="fs-4 fw-bold">{{ $stats['total_parents'] ?? 0 }}</div>
                        <div>أولياء الأمور</div>
                    </div>
                    <i data-feather="briefcase" class="opacity-50" style="font-size: 2.5rem;"></i>
                 </div>
                 <div class="card-footer d-flex align-items-center justify-content-between small">
                    <a class="text-white stretched-link" href="{{ route('admin.users.index', ['role' => 'Parent']) }}">عرض التفاصيل</a>
                    <div class="text-white"><i data-feather="chevron-left"></i></div>
                </div>
            </div>
        </div>

        {{-- إجمالي الفصول --}}
         <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                     <div>
                        <div class="fs-4 fw-bold">{{ $stats['total_classes'] ?? 0 }}</div>
                        <div>الفصول الدراسية</div>
                    </div>
                    <i data-feather="layers" class="opacity-50" style="font-size: 2.5rem;"></i>
                 </div>
                 <div class="card-footer d-flex align-items-center justify-content-between small">
                    <a class="text-white stretched-link" href="{{ route('admin.classes.index') }}">عرض التفاصيل</a>
                    <div class="text-white"><i data-feather="chevron-left"></i></div>
                </div>
            </div>
        </div>

         {{-- الفعاليات القادمة --}}
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-dark h-100"> {{-- استخدام text-dark لخلفية فاتحة --}}
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fs-4 fw-bold">{{ $stats['upcoming_events'] ?? 0 }}</div>
                        <div>الفعاليات القادمة</div>
                    </div>
                    <i data-feather="calendar" class="opacity-50" style="font-size: 2.5rem;"></i>
                 </div>
                 <div class="card-footer d-flex align-items-center justify-content-between small">
                    <a class="text-dark stretched-link" href="{{ route('admin.events.index') }}">عرض التفاصيل</a>
                    <div class="text-dark"><i data-feather="chevron-left"></i></div>
                </div>
            </div>
        </div>
    </div>

    {{-- قسم إحصائيات الحضور لليوم --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                   <i data-feather="check-square" class="me-1"></i> إحصائيات الحضور لليوم ({{ now()->format('Y-m-d') }})
                   <a href="{{ route('admin.attendance.index', ['date' => now()->format('Y-m-d')]) }}" class="float-end btn btn-sm btn-outline-primary">عرض سجل اليوم</a>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col">
                            <div class="fs-5 fw-bold text-success">{{ $attendanceToday['present'] ?? 0 }}</div>
                            <div class="text-muted small">حاضر</div>
                        </div>
                        <div class="col">
                             <div class="fs-5 fw-bold text-danger">{{ $attendanceToday['absent'] ?? 0 }}</div>
                            <div class="text-muted small">غائب</div>
                        </div>
                        <div class="col">
                             <div class="fs-5 fw-bold text-warning">{{ $attendanceToday['late'] ?? 0 }}</div>
                            <div class="text-muted small">متأخر</div>
                        </div>
                         <div class="col">
                             <div class="fs-5 fw-bold">{{ $attendanceToday['total_recorded'] ?? 0 }} / {{ $attendanceToday['total_enrolled'] ?? 0 }}</div>
                            <div class="text-muted small">إجمالي المسجلين</div>
                        </div>
                         <div class="col">
                            <div class="fs-5 fw-bold">{{ $attendanceToday['present_percentage'] ?? 0 }}%</div>
                            <div class="text-muted small">نسبة الحضور</div>
                        </div>
                    </div>
                    @if($attendanceToday['total_recorded'] < $attendanceToday['total_enrolled'])
                    <div class="alert alert-warning small mt-3 mb-0" role="alert">
                       <i data-feather="alert-triangle" class="me-1 align-text-bottom"></i>
                       لم يتم تسجيل الحضور لجميع الأطفال المسجلين اليوم.
                       <a href="{{ route('admin.attendance.index', ['date' => now()->format('Y-m-d')]) }}" class="alert-link">اضغط هنا لتسجيل الحضور</a>.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- قسم الأنشطة الأخيرة --}}
    <div class="row g-4">
        {{-- أحدث الأطفال المسجلين --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <i data-feather="user-plus" class="me-1"></i> أحدث الأطفال المسجلين
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @forelse ($recentActivities['latest_children'] as $child)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <a href="{{ route('admin.children.show', $child) }}">{{ $child->full_name }}</a>
                                    <small class="d-block text-muted">{{ $child->kindergartenClass->class_name ?? 'فصل غير محدد' }}</small>
                                </div>
                                <span class="text-muted small">{{ $child->created_at->diffForHumans() }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted text-center">لا يوجد أطفال جُدد مؤخرًا.</li>
                        @endforelse
                    </ul>
                </div>
                 <div class="card-footer text-center">
                    <a href="{{ route('admin.children.index') }}" class="small">عرض كل الأطفال</a>
                </div>
            </div>
        </div>

        {{-- أحدث الإعلانات --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <i data-feather="bell" class="me-1"></i> أحدث الإعلانات
                </div>
                 <div class="card-body">
                     <ul class="list-group list-group-flush">
                         @forelse ($recentActivities['latest_announcements'] as $announcement)
                            <li class="list-group-item">
                                <a href="{{ route('admin.announcements.show', $announcement) }}">{{ Str::limit($announcement->title, 40) }}</a>
                                <small class="d-block text-muted">
                                    بواسطة: {{ $announcement->author->user->name ?? 'N/A' }} -
                                    {{-- التحويل إلى Carbon قبل استخدام diffForHumans --}}
                                    {{ $announcement->publish_date ? \Carbon\Carbon::parse($announcement->publish_date)->diffForHumans() : $announcement->created_at->diffForHumans() }}
                                    @if($announcement->targetClass) <span class="badge bg-light text-dark border ms-1">{{ $announcement->targetClass->class_name }}</span> @else <span class="badge bg-secondary ms-1">عام</span> @endif
                                </small>
                            </li>
                         @empty
                             <li class="list-group-item text-muted text-center">لا توجد إعلانات جديدة.</li>
                         @endforelse
                    </ul>
                 </div>
                 <div class="card-footer text-center">
                    <a href="{{ route('admin.announcements.index') }}" class="small">عرض كل الإعلانات</a>
                </div>
            </div>
        </div>

        {{-- الفعاليات القادمة --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                   <i data-feather="calendar" class="me-1"></i> الفعاليات القادمة
                </div>
                <div class="card-body">
                     <ul class="list-group list-group-flush">
                         @forelse ($recentActivities['upcoming_events_list'] as $event)
                            <li class="list-group-item">
                                <a href="{{ route('admin.events.show', $event) }}">{{ $event->event_name }}</a>
                                <small class="d-block text-muted">
                                    التاريخ: {{ \Carbon\Carbon::parse($event->event_date)->format('Y-m-d H:i') }}
                                    ({{ \Carbon\Carbon::parse($event->event_date)->diffForHumans() }})
                                </small>
                            </li>
                         @empty
                              <li class="list-group-item text-muted text-center">لا توجد فعاليات قادمة.</li>
                         @endforelse
                    </ul>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('admin.events.index') }}" class="small">عرض كل الفعاليات</a>
                </div>
            </div>
        </div>
    </div> {{-- نهاية صف الأنشطة الأخيرة --}}

</div>
@endsection