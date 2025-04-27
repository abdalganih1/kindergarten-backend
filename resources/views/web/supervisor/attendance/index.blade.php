@extends('layouts.supervisor') {{-- أو layouts.admin --}}

@section('title', 'سجلات الحضور والغياب (فصولي)')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">سجلات الحضور والغياب للفصول التي تشرف عليها</h2>

    {{-- رسالة إذا لم يكن المشرف مسؤولاً عن أي فصول --}}
    @if(isset($noClassesAssigned) && $noClassesAssigned)
        <div class="alert alert-warning" role="alert">
            لم يتم تعيين أي فصول دراسية لك حتى الآن. لا يمكنك إدارة الحضور والغياب.
        </div>
    @else
        {{-- قسم الفلترة والبحث (يعرض فقط فصول المشرف) --}}
        <div class="card mb-4">
            <div class="card-header">
               <i data-feather="filter" class="me-1"></i> عرض السجلات حسب
            </div>
            <div class="card-body">
                 <form method="GET" action="{{ route('supervisor.attendance.index') }}" class="row g-3 align-items-end">
                    {{-- فلتر التاريخ --}}
                    <div class="col-md-4">
                        <label for="date" class="form-label">التاريخ:</label>
                        <input type="date" class="form-control form-control-sm" id="date" name="date" value="{{ $selectedDate ?? old('date', now()->format('Y-m-d')) }}" onchange="this.form.submit()">
                    </div>

                    {{-- فلتر الفصل (فصول المشرف فقط) --}}
                    <div class="col-md-4">
                        <label for="class_id" class="form-label">الفصل الدراسي:</label>
                        <select class="form-select form-select-sm" id="class_id" name="class_id" onchange="this.form.submit()">
                            <option value="">-- كل فصولي --</option>
                            @foreach($supervisedClasses as $id => $name)
                                <option value="{{ $id }}" {{ $selectedClassId == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                     {{-- حقل البحث --}}
                    <div class="col-md-4">
                        <label for="search" class="form-label">بحث عن طفل:</label>
                        <input type="text" class="form-control form-control-sm" id="search" name="search" placeholder="اسم الطفل..." value="{{ $searchTerm ?? '' }}">
                        <button type="submit" class="btn btn-link p-0 border-0 text-primary" style="position: absolute; left: 20px; bottom: 5px;"><i data-feather="search"></i></button>
                    </div>

                </form>
            </div>
        </div>

        {{-- زر تسجيل حضور جماعي --}}
        <div class="mb-3">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#batchAttendanceModalSupervisor">
                <i data-feather="plus-circle" class="me-1"></i> تسجيل حضور جماعي لفصل
            </button>
        </div>


        {{-- جدول عرض سجلات الحضور --}}
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    سجلات الحضور لـ: {{ $selectedDate }}
                     @if($selectedClassId && $supervisedClasses->has($selectedClassId))
                     - {{ $supervisedClasses->get($selectedClassId) }}
                    @endif
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>اسم الطفل</th>
                                @if(!$selectedClassId)<th>الفصل</th>@endif {{-- إظهار الفصل إذا لم يتم الفلترة به --}}
                                <th>الحالة</th>
                                <th>وقت الدخول</th>
                                <th>وقت الخروج</th>
                                <th>ملاحظات</th>
                                <th>سُجّل بواسطة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($attendances as $index => $attendance)
                            <tr>
                                <td>{{ $attendances->firstItem() + $index }}</td>
                                <td>{{ $attendance->child->full_name ?? 'N/A' }}</td>
                                @if(!$selectedClassId)<td>{{ $attendance->child->kindergartenClass->class_name ?? 'N/A' }}</td>@endif
                                <td>
                                    @php $statusClass = match($attendance->status) { 'Present' => 'success', 'Absent' => 'danger', 'Late' => 'warning', 'Excused' => 'info', default => 'secondary' }; @endphp
                                    <span class="badge bg-{{ $statusClass }}">{{ $attendance->status }}</span>
                                </td>
                                <td>{{ $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') : '-' }}</td>
                                <td>{{ $attendance->check_out_time ? \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i') : '-' }}</td>
                                <td>{{ $attendance->notes ?: '-' }}</td>
                                <td>{{ $attendance->recordedByUser->name ?? 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('supervisor.attendance.edit', $attendance->attendance_id) }}" class="btn btn-sm btn-warning me-1" title="تعديل">
                                        <i data-feather="edit-2"></i>
                                    </a>
                                    <form action="{{ route('supervisor.attendance.destroy', $attendance->attendance_id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا السجل؟');">
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
                                <td colspan="{{ !$selectedClassId ? 9 : 8 }}" class="text-center py-4">لا توجد سجلات حضور مطابقة لهذا التاريخ أو الفلترة.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($attendances->hasPages())
                <div class="card-footer">
                    {{ $attendances->links() }}
                </div>
            @endif
        </div> {{-- نهاية الـ card --}}

        {{-- Modal لتسجيل الحضور الجماعي (للمشرف) --}}
        <div class="modal fade" id="batchAttendanceModalSupervisor" tabindex="-1" aria-labelledby="batchAttendanceModalLabelSupervisor" aria-hidden="true">
          <div class="modal-dialog">
            <form method="GET" action="{{ route('supervisor.attendance.createBatchForm') }}"> {{-- توجيه إلى المسار الذي يعرض النموذج --}}
              <div class="modal-content">
                <div class="modal-header">
                  <h1 class="modal-title fs-5" id="batchAttendanceModalLabelSupervisor">اختيار الفصل والتاريخ</h1>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="batch_date_sup" class="form-label">التاريخ:</label>
                        <input type="date" class="form-control" id="batch_date_sup" name="date" value="{{ $selectedDate ?? now()->format('Y-m-d') }}" required>
                    </div>
                     <div class="mb-3">
                        <label for="batch_class_id_sup" class="form-label">الفصل الدراسي:</label>
                        <select class="form-select" id="batch_class_id_sup" name="class_id" required>
                            <option value="" disabled selected>-- اختر الفصل --</option>
                            @foreach($supervisedClasses as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                  <button type="submit" class="btn btn-primary">عرض نموذج التسجيل</button>
                </div>
              </div>
            </form>
          </div>
        </div>
    @endif {{-- نهاية التحقق من وجود فصول --}}
</div>
@endsection