@extends('layouts.supervisor') {{-- أو layouts.admin --}}

@section('title', 'تسجيل حضور جماعي - ' . $classInfo->class_name . ' (' . $attendanceDate . ')')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">تسجيل الحضور والغياب للفصل: <span class="text-primary">{{ $classInfo->class_name }}</span></h2>
    <h4 class="mb-4">التاريخ: <span class="text-secondary">{{ $attendanceDate }}</span></h4>

    <a href="{{ route('supervisor.attendance.index', ['date' => $attendanceDate, 'class_id' => $classInfo->class_id]) }}" class="btn btn-secondary mb-3">
        <i data-feather="arrow-right" class="me-1"></i> العودة إلى القائمة
    </a>

    <form action="{{ route('supervisor.attendance.store') }}" method="POST"> {{-- استخدام مسار المشرف --}}
        @csrf
        <input type="hidden" name="attendance_date" value="{{ $attendanceDate }}">

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>اسم الطفل</th>
                        <th width="15%">الحالة <span class="text-danger">*</span></th>
                        <th width="15%">وقت الدخول</th>
                        <th width="15%">وقت الخروج</th>
                        <th>ملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($children as $index => $child)
                        @php $currentAttendance = $child->attendances->first(); @endphp
                        <tr>
                            <td>
                                {{ $child->full_name }}
                                <input type="hidden" name="attendance[{{ $index }}][child_id]" value="{{ $child->child_id }}">
                            </td>
                            <td>
                                <select name="attendance[{{ $index }}][status]" class="form-select form-select-sm" required>
                                    @foreach($statuses as $value => $label)
                                        <option value="{{ $value }}" {{ old('attendance.'.$index.'.status', $currentAttendance->status ?? 'Present') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                             <td><input type="time" name="attendance[{{ $index }}][check_in_time]" class="form-control form-control-sm" value="{{ old('attendance.'.$index.'.check_in_time', $currentAttendance->check_in_time ?? '') }}"></td>
                            <td><input type="time" name="attendance[{{ $index }}][check_out_time]" class="form-control form-control-sm" value="{{ old('attendance.'.$index.'.check_out_time', $currentAttendance->check_out_time ?? '') }}"></td>
                             <td><input type="text" name="attendance[{{ $index }}][notes]" class="form-control form-control-sm" placeholder="ملاحظات..." value="{{ old('attendance.'.$index.'.notes', $currentAttendance->notes ?? '') }}"></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">لا يوجد أطفال مسجلون في هذا الفصل.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($children->isNotEmpty())
        <div class="mt-3 d-flex justify-content-end">
            <button type="submit" class="btn btn-primary btn-lg">
                <i data-feather="save" class="me-1"></i> حفظ سجلات الحضور
            </button>
        </div>
        @endif
    </form>

</div>
@endsection