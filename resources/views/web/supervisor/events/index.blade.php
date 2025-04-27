@extends('layouts.supervisor') {{-- أو layouts.admin --}}

@section('title', 'عرض الفعاليات والرحلات')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">الفعاليات والرحلات</h2>

    {{-- قسم الفلترة والبحث (مطابق للمدير) --}}
    <div class="card mb-4">
        <div class="card-header">
           <i data-feather="filter" class="me-1"></i> فلترة وعرض الفعاليات
        </div>
        <div class="card-body">
             <form method="GET" action="{{ route('supervisor.events.index') }}" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="search" class="form-label">بحث بالاسم/الموقع:</label>
                    <input type="text" class="form-control form-control-sm" id="search" name="search" placeholder="اسم الفعالية أو مكانها..." value="{{ $searchTerm ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label for="status" class="form-label">عرض الفعاليات:</label>
                    <select class="form-select form-select-sm" id="status" name="status">
                        <option value="upcoming" {{ $status == 'upcoming' ? 'selected' : '' }}>القادمة</option>
                        <option value="past" {{ $status == 'past' ? 'selected' : '' }}>المنتهية</option>
                        <option value="all" {{ $status == 'all' ? 'selected' : '' }}>الكل</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-sm w-100">تطبيق</button>
                </div>
            </form>
        </div>
    </div>


    {{-- جدول عرض الفعاليات (بدون أزرار تعديل/حذف) --}}
    <div class="card">
         <div class="card-header">
            <h5 class="card-title mb-0">قائمة الفعاليات ({{ match($status) { 'upcoming' => 'القادمة', 'past' => 'المنتهية', default => 'الكل' } }})</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>اسم الفعالية</th>
                            <th>التاريخ والوقت</th>
                            <th>الموقع</th>
                            <th>يتطلب تسجيل؟</th>
                            <th>الموعد النهائي</th>
                            <th>عدد المسجلين</th>
                            <th>أنشئت بواسطة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($events as $index => $event)
                        <tr>
                            <td>{{ $events->firstItem() + $index }}</td>
                            <td>
                                {{-- رابط العرض للمشرف --}}
                                <a href="{{ route('supervisor.events.show', $event) }}">{{ $event->event_name }}</a>
                            </td>
                            <td>{{ $event->event_date ? \Carbon\Carbon::parse($event->event_date)->format('Y-m-d H:i') : 'N/A' }}</td>
                            <td>{{ $event->location ?? '-' }}</td>
                            <td>@if($event->requires_registration)<span class="badge bg-success">نعم</span>@else<span class="badge bg-secondary">لا</span>@endif</td>
                             <td>{{ $event->registration_deadline ? \Carbon\Carbon::parse($event->registration_deadline)->format('Y-m-d H:i') : '-' }}</td>
                             <td>{{ $event->registrations_count ?? '0' }}</td>
                             <td>{{ $event->createdByAdmin->user->name ?? 'N/A' }}</td>
                            <td>
                                {{-- زر العرض فقط --}}
                                <a href="{{ route('supervisor.events.show', $event) }}" class="btn btn-sm btn-info" title="عرض التفاصيل والتسجيلات">
                                    <i data-feather="eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">لا توجد فعاليات تطابق الفلترة الحالية.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
         @if ($events->hasPages())
            <div class="card-footer">
                {{ $events->links() }}
            </div>
        @endif
    </div> {{-- نهاية الـ card --}}
</div>
@endsection