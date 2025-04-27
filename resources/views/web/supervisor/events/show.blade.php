@extends('layouts.supervisor') {{-- أو layouts.admin --}}

@section('title', 'تفاصيل الفعالية: ' . $event->event_name)

@section('header-buttons')
    {{-- لا يوجد أزرار تعديل أو حذف للمشرف --}}
    <a href="{{ route('supervisor.events.index') }}" class="btn btn-sm btn-outline-secondary">
        <i data-feather="arrow-left" class="me-1"></i> العودة إلى القائمة
    </a>
@endsection

@section('content')
<div class="container-fluid">
     <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">تفاصيل الفعالية</h5>
        </div>
        <div class="card-body">
             {{-- نفس محتوى عرض المدير dl... --}}
             <h3 class="card-title border-bottom pb-2 mb-3">{{ $event->event_name }}</h3>
             <dl class="row mb-0">
                <dt class="col-sm-3">الوصف:</dt> <dd class="col-sm-9">{{ $event->description ?: '-' }}</dd>
                <dt class="col-sm-3">التاريخ والوقت:</dt> <dd class="col-sm-9">{{ $event->event_date ? \Carbon\Carbon::parse($event->event_date)->format('Y-m-d H:i A') : 'N/A' }}</dd>
                <dt class="col-sm-3">الموقع:</dt> <dd class="col-sm-9">{{ $event->location ?: '-' }}</dd>
                <dt class="col-sm-3">يتطلب تسجيل:</dt> <dd class="col-sm-9">@if($event->requires_registration)<span class="badge bg-success">نعم</span>@else<span class="badge bg-secondary">لا</span>@endif</dd>
                @if($event->requires_registration)<dt class="col-sm-3">الموعد النهائي للتسجيل:</dt> <dd class="col-sm-9">{{ $event->registration_deadline ? \Carbon\Carbon::parse($event->registration_deadline)->format('Y-m-d H:i A') : '-' }}</dd>@endif
                <dt class="col-sm-3">أنشئت بواسطة:</dt> <dd class="col-sm-9">{{ $event->createdByAdmin->user->name ?? 'N/A' }}</dd>
                <dt class="col-sm-3">تاريخ الإنشاء:</dt> <dd class="col-sm-9">{{ $event->created_at->format('Y-m-d H:i A') }}</dd>
             </dl>
        </div>
    </div>

     {{-- قسم تسجيلات الأطفال (نفس قسم المدير للعرض) --}}
     @if($event->requires_registration)
         <div class="card">
             <div class="card-header">
                <h5 class="card-title mb-0"><i data-feather="list" class="me-1"></i> الأطفال المسجلون ({{ $registrations->total() }})</h5>
             </div>
             <div class="card-body p-0">
                <div class="table-responsive">
                     <table class="table table-striped table-hover table-bordered mb-0">
                        <thead class="table-dark">
                            <tr><th>#</th><th>اسم الطفل</th><th>الفصل</th><th>تاريخ التسجيل</th><th>موافقة ولي الأمر</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($registrations as $index => $reg)
                            <tr>
                                <td>{{ $registrations->firstItem() + $index }}</td>
                                <td>{{ $reg->child->full_name ?? 'N/A' }}</td>
                                <td>{{ $reg->child->kindergartenClass->class_name ?? 'N/A' }}</td>
                                <td>{{ $reg->registration_date->format('Y-m-d H:i') }}</td>
                                <td>@if($reg->parent_consent)<span class="badge bg-success">نعم</span>@else<span class="badge bg-warning">لا</span>@endif</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center py-4">لا يوجد أطفال مسجلون في هذه الفعالية بعد.</td></tr>
                            @endforelse
                        </tbody>
                     </table>
                </div>
            </div>
            @if ($registrations->hasPages())
                <div class="card-footer">
                    {{ $registrations->appends(request()->except('regs_page'))->links() }} {{-- تعديل بسيط للحفاظ على فلاتر الصفحة الرئيسية إذا وجدت --}}
                </div>
            @endif
         </div>
     @endif
</div>
@endsection