@extends('layouts.admin') {{-- يفترض وجود layout أساسي --}}

@section('title', 'سجلات الحضور والغياب') {{-- تحديد عنوان الصفحة --}}

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">سجلات الحضور والغياب</h2>

    {{-- قسم الفلترة والبحث --}}
    <form method="GET" action="{{ route('admin.attendance.index') }}" class="mb-4 p-3 bg-light border rounded">
        <div class="row g-3 align-items-end">
            {{-- فلتر التاريخ --}}
            <div class="col-md-3">
                <label for="date" class="form-label">التاريخ:</label>
                <input type="date" class="form-control" id="date" name="date" value="{{ $selectedDate ?? old('date', now()->format('Y-m-d')) }}">
            </div>

            {{-- فلتر الفصل --}}
            <div class="col-md-3">
                <label for="class_id" class="form-label">الفصل الدراسي:</label>
                <select class="form-select" id="class_id" name="class_id">
                    <option value="">-- الكل --</option>
                    @foreach($classes as $id => $name)
                        <option value="{{ $id }}" {{ $selectedClassId == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- حقل البحث --}}
            <div class="col-md-3">
                <label for="search" class="form-label">بحث عن طفل:</label>
                <input type="text" class="form-control" id="search" name="search" placeholder="اسم الطفل..." value="{{ $searchTerm ?? '' }}">
            </div>

            {{-- زر تطبيق الفلتر --}}
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">تطبيق الفلتر</button>
            </div>
        </div>
    </form>

    {{-- زر تسجيل حضور جماعي (يفتح نموذج منفصل أو صفحة) --}}
    <div class="mb-3">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#batchAttendanceModal">
            <i data-feather="plus-circle" class="me-1"></i> تسجيل حضور جماعي لفصل
        </button>
         {{-- يمكنك أيضًا استخدام رابط لصفحة create_batch --}}
        {{-- <a href="{{ route('admin.attendance.createBatchForm') }}" class="btn btn-success">
            <i data-feather="plus-circle" class="me-1"></i> تسجيل حضور جماعي لفصل
        </a> --}}
    </div>

    {{-- جدول عرض سجلات الحضور --}}
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>اسم الطفل</th>
                    <th>الفصل</th>
                    <th>التاريخ</th>
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
                    <td>{{ $attendances->firstItem() + $index }}</td> {{-- ترقيم مع الـ pagination --}}
                    <td>{{ $attendance->child->full_name ?? 'N/A' }}</td>
                    <td>{{ $attendance->child->kindergartenClass->class_name ?? 'N/A' }}</td>
                    <td>{{ \Carbon\Carbon::parse($attendance->attendance_date)->format('Y-m-d') }}</td>
                    <td>
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
                    </td>
                    <td>{{ $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') : '-' }}</td>
                    <td>{{ $attendance->check_out_time ? \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i') : '-' }}</td>
                    <td>{{ $attendance->notes ?: '-' }}</td>
                    <td>{{ $attendance->recordedByUser->name ?? 'N/A' }}</td>
                    <td>
                        <a href="{{ route('admin.attendance.edit', $attendance->attendance_id) }}" class="btn btn-sm btn-warning me-1" title="تعديل">
                            <i data-feather="edit-2"></i>
                        </a>
                        {{-- زر الحذف مع تأكيد --}}
                        <form action="{{ route('admin.attendance.destroy', $attendance->attendance_id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا السجل؟');">
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
                    <td colspan="10" class="text-center">لا توجد سجلات حضور مطابقة لهذا التاريخ أو الفلترة.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- روابط الـ Pagination --}}
    <div class="mt-3">
        {{ $attendances->links() }}
    </div>

</div>


{{-- Modal لتسجيل الحضور الجماعي --}}
<div class="modal fade" id="batchAttendanceModal" tabindex="-1" aria-labelledby="batchAttendanceModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="GET" action="{{ route('admin.attendance.createBatchForm') }}"> {{-- توجيه إلى المسار الذي يعرض النموذج --}}
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="batchAttendanceModalLabel">اختيار الفصل والتاريخ</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label for="batch_date" class="form-label">التاريخ:</label>
                <input type="date" class="form-control" id="batch_date" name="date" value="{{ $selectedDate ?? now()->format('Y-m-d') }}" required>
            </div>
             <div class="mb-3">
                <label for="batch_class_id" class="form-label">الفصل الدراسي:</label>
                <select class="form-select" id="batch_class_id" name="class_id" required>
                    <option value="" disabled selected>-- اختر الفصل --</option>
                    @foreach($classes as $id => $name)
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
@endsection