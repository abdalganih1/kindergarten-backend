@extends('layouts.admin')

@section('title', 'إدارة الفعاليات والرحلات')

@section('header-buttons')
<a href="{{ route('admin.events.create') }}" class="btn btn-sm btn-success">
    <i data-feather="plus-circle" class="me-1"></i> إضافة فعالية جديدة
</a>
@endsection

@section('content')
<div class="container-fluid">

    {{-- قسم الفلترة والبحث --}}
    <div class="card mb-4">
        <div class="card-header">
           <i data-feather="filter" class="me-1"></i> فلترة وعرض الفعاليات
        </div>
        <div class="card-body">
             <form method="GET" action="{{ route('admin.events.index') }}" class="row g-3 align-items-end">
                {{-- حقل البحث --}}
                <div class="col-md-5">
                    <label for="search" class="form-label">بحث بالاسم/الموقع:</label>
                    <input type="text" class="form-control form-control-sm" id="search" name="search" placeholder="اسم الفعالية أو مكانها..." value="{{ $searchTerm ?? '' }}">
                </div>

                {{-- فلتر الحالة (قادمة/منتهية) --}}
                <div class="col-md-4">
                    <label for="status" class="form-label">عرض الفعاليات:</label>
                    <select class="form-select form-select-sm" id="status" name="status">
                        <option value="upcoming" {{ $status == 'upcoming' ? 'selected' : '' }}>القادمة</option>
                        <option value="past" {{ $status == 'past' ? 'selected' : '' }}>المنتهية</option>
                        <option value="all" {{ $status == 'all' ? 'selected' : '' }}>الكل</option>
                    </select>
                </div>

                {{-- زر تطبيق الفلتر --}}
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-sm w-100">تطبيق</button>
                </div>
            </form>
        </div>
    </div>


    {{-- جدول عرض الفعاليات --}}
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
                                <a href="{{ route('admin.events.show', $event) }}">{{ $event->event_name }}</a>
                            </td>
                            <td>{{ $event->event_date ? \Carbon\Carbon::parse($event->event_date)->format('Y-m-d H:i') : 'N/A' }}</td>
                            <td>{{ $event->location ?? '-' }}</td>
                            <td>
                                @if($event->requires_registration)
                                    <span class="badge bg-success">نعم</span>
                                @else
                                    <span class="badge bg-secondary">لا</span>
                                @endif
                            </td>
                             <td>{{ $event->registration_deadline ? \Carbon\Carbon::parse($event->registration_deadline)->format('Y-m-d H:i') : '-' }}</td>
                             <td>{{ $event->registrations_count ?? '0' }}</td> {{-- استخدام العدد المحسوب --}}
                             <td>{{ $event->createdByAdmin->user->name ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('admin.events.show', $event) }}" class="btn btn-sm btn-info me-1" title="عرض التفاصيل والتسجيلات">
                                    <i data-feather="eye"></i>
                                </a>
                                <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-sm btn-warning me-1" title="تعديل">
                                    <i data-feather="edit-2"></i>
                                </a>
                                <form action="{{ route('admin.events.destroy', $event) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه الفعالية وجميع تسجيلاتها؟');">
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