@extends('layouts.admin')

@section('title', 'تفاصيل الفعالية: ' . $event->event_name)

@section('header-buttons')
    <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-sm btn-warning me-2">
        <i data-feather="edit-2" class="me-1"></i> تعديل الفعالية
    </a>
     <form action="{{ route('admin.events.destroy', $event) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه الفعالية وجميع تسجيلاتها؟');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger me-2">
            <i data-feather="trash-2" class="me-1"></i> حذف الفعالية
        </button>
    </form>
    <a href="{{ route('admin.events.index') }}" class="btn btn-sm btn-outline-secondary">
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
            <h3 class="card-title border-bottom pb-2 mb-3">{{ $event->event_name }}</h3>
            <dl class="row mb-0">
                <dt class="col-sm-3">الوصف:</dt>
                <dd class="col-sm-9">{{ $event->description ?: '-' }}</dd>

                <dt class="col-sm-3">التاريخ والوقت:</dt>
                <dd class="col-sm-9">{{ $event->event_date ? \Carbon\Carbon::parse($event->event_date)->format('Y-m-d H:i A') : 'N/A' }}</dd>

                <dt class="col-sm-3">الموقع:</dt>
                <dd class="col-sm-9">{{ $event->location ?: '-' }}</dd>

                <dt class="col-sm-3">يتطلب تسجيل:</dt>
                 <dd class="col-sm-9">
                    @if($event->requires_registration)
                        <span class="badge bg-success">نعم</span>
                    @else
                        <span class="badge bg-secondary">لا</span>
                    @endif
                </dd>

                 @if($event->requires_registration)
                    <dt class="col-sm-3">الموعد النهائي للتسجيل:</dt>
                    <dd class="col-sm-9">{{ $event->registration_deadline ? \Carbon\Carbon::parse($event->registration_deadline)->format('Y-m-d H:i A') : '-' }}</dd>
                 @endif

                 <dt class="col-sm-3">أنشئت بواسطة:</dt>
                 <dd class="col-sm-9">{{ $event->createdByAdmin->user->name ?? 'N/A' }}</dd>

                 <dt class="col-sm-3">تاريخ الإنشاء:</dt>
                 <dd class="col-sm-9">{{ $event->created_at->format('Y-m-d H:i A') }}</dd>

                 <dt class="col-sm-3">آخر تحديث:</dt>
                 <dd class="col-sm-9">{{ $event->updated_at->format('Y-m-d H:i A') }}</dd>
            </dl>
        </div>
    </div>

     {{-- قسم تسجيلات الأطفال --}}
     @if($event->requires_registration)
         <div class="card">
             <div class="card-header">
                <h5 class="card-title mb-0"><i data-feather="list" class="me-1"></i> الأطفال المسجلون ({{ $registrations->total() }})</h5>
             </div>
             <div class="card-body p-0">
                <div class="table-responsive">
                     <table class="table table-striped table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>اسم الطفل</th>
                                <th>الفصل</th>
                                <th>تاريخ التسجيل</th>
                                <th>موافقة ولي الأمر</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($registrations as $index => $reg)
                            <tr>
                                <td>{{ $registrations->firstItem() + $index }}</td>
                                <td>{{ $reg->child->full_name ?? 'N/A' }}</td>
                                <td>{{ $reg->child->kindergartenClass->class_name ?? 'N/A' }}</td>
                                <td>{{ $reg->registration_date->format('Y-m-d H:i') }}</td>
                                <td>
                                     @if($reg->parent_consent)
                                        <span class="badge bg-success">نعم</span>
                                    @else
                                        <span class="badge bg-warning">لا</span>
                                    @endif
                                </td>
                                <td>
                                     {{-- يمكنك إضافة زر لحذف تسجيل معين إذا لزم الأمر --}}
                                     <form action="{{ route('admin.event-registrations.destroy', $reg->registration_id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من إلغاء تسجيل هذا الطفل؟');"> {{-- تأكد من وجود هذا المسار --}}
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="إلغاء التسجيل">
                                            <i data-feather="user-x"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">لا يوجد أطفال مسجلون في هذه الفعالية بعد.</td>
                            </tr>
                            @endforelse
                        </tbody>
                     </table>
                </div>
            </div>
            @if ($registrations->hasPages())
                <div class="card-footer">
                    {{-- استخدام اسم الصفحة المخصص للـ pagination --}}
                    {{ $registrations->links() }}
                </div>
            @endif
         </div>
     @endif
</div>
@endsection