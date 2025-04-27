@extends('layouts.supervisor') {{-- أو layouts.admin --}}

@section('title', 'ملف الطفل: ' . $child->full_name)

@section('header-buttons')
    {{-- لا يوجد أزرار تعديل أو حذف للمشرف هنا --}}
    <a href="{{ route('supervisor.children.index') }}" class="btn btn-sm btn-outline-secondary">
        <i data-feather="arrow-left" class="me-1"></i> العودة إلى القائمة
    </a>
@endsection

@section('content')
<div class="container-fluid">

    <div class="row">
        {{-- Child Info Card --}}
        <div class="col-lg-4 mb-4">
            <div class="card text-center h-100">
                 <div class="card-header">
                     <h5 class="card-title mb-0">ملف الطفل</h5>
                 </div>
                <div class="card-body">
                    <img src="{{ $child->photo_url ? Storage::disk('public')->url($child->photo_url) : asset('images/default-avatar.png') }}"
                         alt="{{ $child->full_name }}"
                         class="rounded-circle img-thumbnail mb-3"
                         width="150" height="150" style="object-fit: cover;">
                    <h4 class="card-title">{{ $child->full_name }}</h4>
                    <p class="card-text text-muted">
                        تاريخ الميلاد: {{ $child->date_of_birth ? \Carbon\Carbon::parse($child->date_of_birth)->format('Y-m-d') : 'N/A' }}
                        ({{ $child->date_of_birth ? \Carbon\Carbon::parse($child->date_of_birth)->age : 'N/A' }} سنوات)
                    </p>
                    <p class="card-text">الجنس: {{ $child->gender ?? 'غير محدد' }}</p>
                     <p class="card-text">
                         الفصل:
                        @if($child->kindergartenClass)
                            <span class="badge bg-info fs-6">{{ $child->kindergartenClass->class_name }}</span>
                        @else
                            <span class="badge bg-secondary fs-6">غير محدد</span>
                        @endif
                    </p>
                     <p class="card-text">
                        تاريخ التسجيل: {{ $child->enrollment_date ? \Carbon\Carbon::parse($child->enrollment_date)->format('Y-m-d') : 'N/A' }}
                     </p>
                </div>
            </div>
        </div>

        {{-- Parents Info Card --}}
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                 <div class="card-header">
                    <h5 class="card-title mb-0"><i data-feather="users" class="me-1"></i> أولياء الأمور</h5>
                 </div>
                <div class="card-body">
                    @forelse ($child->parents as $parent)
                        <div class="border-bottom pb-2 mb-2">
                             <p class="mb-1"><strong>الاسم:</strong> {{ $parent->full_name }}</p>
                            <p class="mb-1"><strong>البريد:</strong> {{ $parent->user->email ?? $parent->contact_email ?? 'N/A' }}</p>
                            <p class="mb-0"><strong>الهاتف:</strong> {{ $parent->contact_phone ?? 'N/A' }}</p>
                        </div>
                    @empty
                        <p class="text-muted">لا يوجد أولياء أمور مرتبطون بهذا الطفل.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Medical Info Card --}}
        <div class="col-lg-4 mb-4">
             <div class="card h-100">
                 <div class="card-header">
                    <h5 class="card-title mb-0"><i data-feather="shield" class="me-1"></i> معلومات طبية</h5>
                 </div>
                 <div class="card-body">
                     <h6>الحساسية:</h6>
                     <p>{{ $child->allergies ?: 'لا يوجد' }}</p>
                     <hr>
                     <h6>ملاحظات طبية:</h6>
                     <p>{{ $child->medical_notes ?: 'لا يوجد' }}</p>
                 </div>
             </div>
        </div>
    </div>

    {{-- Tabs for other details (مشابهة تمامًا لواجهة المدير) --}}
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="childDetailsTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance-content" type="button" role="tab" aria-controls="attendance-content" aria-selected="true">
                         <i data-feather="check-square" class="me-1"></i> الحضور والغياب
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="health-tab" data-bs-toggle="tab" data-bs-target="#health-content" type="button" role="tab" aria-controls="health-content" aria-selected="false">
                        <i data-feather="activity" class="me-1"></i> السجلات الصحية
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="events-tab" data-bs-toggle="tab" data-bs-target="#events-content" type="button" role="tab" aria-controls="events-content" aria-selected="false">
                         <i data-feather="calendar" class="me-1"></i> تسجيل الفعاليات
                    </button>
                </li>
                 <li class="nav-item" role="presentation">
                    <button class="nav-link" id="observations-tab" data-bs-toggle="tab" data-bs-target="#observations-content" type="button" role="tab" aria-controls="observations-content" aria-selected="false">
                         <i data-feather="eye" class="me-1"></i> ملاحظات أولياء الأمور
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="childDetailsTabContent">
                {{-- Attendance Tab --}}
                <div class="tab-pane fade show active" id="attendance-content" role="tabpanel" aria-labelledby="attendance-tab">
                    <h5 class="mb-3">سجل الحضور والغياب (الأحدث أولاً)</h5>
                    {{-- رابط لسجل الحضور الخاص بهذا الطفل --}}
                    <a href="{{ route('supervisor.attendance.index', ['search' => $child->full_name]) }}" class="btn btn-sm btn-outline-secondary mb-2">عرض كل سجلات الحضور لهذا الطفل</a>
                     <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        {{-- نفس جدول الحضور الموجود في ملف عرض المدير --}}
                        <table class="table table-sm table-bordered table-striped">
                            <thead><tr><th>التاريخ</th><th>الحالة</th><th>وقت الدخول</th><th>وقت الخروج</th><th>ملاحظات</th><th>سُجّل بواسطة</th></tr></thead>
                            <tbody>
                                @forelse($child->attendances()->latest('attendance_date')->take(10)->get() as $att) {{-- عرض آخر 10 سجلات هنا فقط --}}
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($att->attendance_date)->format('Y-m-d') }}</td>
                                        <td>
                                            @php $statusClass = match($att->status) { 'Present' => 'success', 'Absent' => 'danger', 'Late' => 'warning', 'Excused' => 'info', default => 'secondary' }; @endphp
                                            <span class="badge bg-{{$statusClass}}">{{ $att->status }}</span>
                                        </td>
                                        <td>{{ $att->check_in_time ? \Carbon\Carbon::parse($att->check_in_time)->format('H:i') : '-' }}</td>
                                        <td>{{ $att->check_out_time ? \Carbon\Carbon::parse($att->check_out_time)->format('H:i') : '-' }}</td>
                                        <td>{{ $att->notes ?: '-' }}</td>
                                        <td>{{ $att->recordedByUser->name ?? 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center">لا توجد سجلات حضور.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Health Records Tab --}}
                <div class="tab-pane fade" id="health-content" role="tabpanel" aria-labelledby="health-tab">
                    <h5 class="mb-3">السجلات الصحية (الأحدث أولاً)</h5>
                    {{-- رابط للسجلات الصحية الخاصة بهذا الطفل (إذا كان للمشرف صلاحية التعديل) --}}
                     <a href="{{ route('supervisor.health-records.index', ['child_id' => $child->child_id]) }}" class="btn btn-sm btn-outline-secondary mb-2">إدارة السجلات الصحية لهذا الطفل</a>
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                         {{-- نفس جدول السجلات الصحية الموجود في ملف عرض المدير --}}
                         <table class="table table-sm table-bordered table-striped">
                             <thead><tr><th>التاريخ</th><th>النوع</th><th>التفاصيل</th><th>الاستحقاق القادم</th><th>أُدخل بواسطة</th></tr></thead>
                             <tbody>
                                 @forelse($child->healthRecords()->latest('record_date')->take(10)->get() as $rec) {{-- عرض آخر 10 سجلات --}}
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($rec->record_date)->format('Y-m-d') }}</td>
                                        <td>{{ $rec->record_type }}</td>
                                        <td>{{ $rec->details }}</td>
                                        <td>{{ $rec->next_due_date ? \Carbon\Carbon::parse($rec->next_due_date)->format('Y-m-d') : '-' }}</td>
                                        <td>{{ $rec->enteredByUser->name ?? 'N/A' }}</td>
                                    </tr>
                                 @empty
                                     <tr><td colspan="5" class="text-center">لا توجد سجلات صحية.</td></tr>
                                 @endforelse
                             </tbody>
                         </table>
                    </div>
                </div>

                {{-- Event Registrations Tab --}}
                <div class="tab-pane fade" id="events-content" role="tabpanel" aria-labelledby="events-tab">
                     <h5 class="mb-3">الفعاليات المسجل بها (الأحدث أولاً)</h5>
                     <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        {{-- نفس جدول تسجيل الفعاليات الموجود في ملف عرض المدير --}}
                         <table class="table table-sm table-bordered table-striped">
                             <thead><tr><th>الفعالية</th><th>تاريخ الفعالية</th><th>تاريخ التسجيل</th><th>موافقة ولي الأمر</th></tr></thead>
                             <tbody>
                                 @forelse($child->eventRegistrations()->latest('registration_date')->get() as $reg)
                                    <tr>
                                        <td>{{ $reg->event->event_name ?? 'N/A' }}</td>
                                        <td>{{ $reg->event->event_date ? \Carbon\Carbon::parse($reg->event->event_date)->format('Y-m-d H:i') : 'N/A' }}</td>
                                        <td>{{ $reg->registration_date->format('Y-m-d H:i') }}</td>
                                        <td>@if($reg->parent_consent)<span class="badge bg-success">نعم</span>@else<span class="badge bg-warning">لا</span>@endif</td>
                                    </tr>
                                 @empty
                                     <tr><td colspan="4" class="text-center">الطفل غير مسجل في أي فعاليات حاليًا.</td></tr>
                                 @endforelse
                             </tbody>
                         </table>
                     </div>
                </div>

                 {{-- Observations Tab --}}
                <div class="tab-pane fade" id="observations-content" role="tabpanel" aria-labelledby="observations-tab">
                     <h5 class="mb-3">ملاحظات أولياء الأمور (الأحدث أولاً)</h5>
                      <div style="max-height: 400px; overflow-y: auto;">
                        @forelse($child->observations()->latest('submitted_at')->get() as $obs)
                            <div class="border rounded p-3 mb-2 bg-light">
                                <p class="mb-1">{{ $obs->observation_text }}</p>
                                <small class="text-muted">
                                    بواسطة: {{ $obs->parentSubmitter->full_name ?? 'N/A' }} -
                                    في: {{ $obs->submitted_at->format('Y-m-d H:i A') }}
                                </small>
                            </div>
                        @empty
                            <p class="text-center text-muted">لا توجد ملاحظات لهذا الطفل.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
{{-- نفس سكريبت تفعيل التبويب الأول إذا أردت --}}
<script>
    var firstTabEl = document.querySelector('#childDetailsTab li:first-child button')
    if(firstTabEl){
        var tab = new bootstrap.Tab(firstTabEl)
        // tab.show()
    }
</script>
@endpush