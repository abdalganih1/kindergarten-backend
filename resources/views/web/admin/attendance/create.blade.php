@extends('layouts.admin')

@section('title', 'إضافة سجل حضور جديد')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">إضافة سجل حضور جديد</h2>

    <div class="card">
         <div class="card-header">
            إضافة سجل ليوم: {{ $attendanceDate }}
        </div>
        <div class="card-body">
             <form action="{{ route('admin.attendance.store') }}" method="POST"> {{-- تحتاج لتعديل دالة store لتمييز الطلب الفردي عن الجماعي أو إنشاء مسار منفصل --}}
                @csrf
                <input type="hidden" name="attendance_date" value="{{ $attendanceDate }}">

                 {{-- اختيار الطفل --}}
                 <div class="mb-3">
                    <label for="child_id" class="form-label">الطفل <span class="text-danger">*</span></label>
                    <select name="attendance[0][child_id]" id="child_id" class="form-select @error('attendance.0.child_id') is-invalid @enderror" required>
                        <option value="">-- اختر الطفل --</option>
                        @foreach($childrenWithoutAttendance as $child)
                            <option value="{{ $child->child_id }}" {{ old('attendance.0.child_id') == $child->child_id ? 'selected' : '' }}>
                                {{ $child->full_name }}
                            </option>
                        @endforeach
                    </select>
                     @error('attendance.0.child_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @if($childrenWithoutAttendance->isEmpty())
                        <small class="text-muted">جميع الأطفال لديهم سجل حضور لهذا اليوم.</small>
                    @endif
                </div>

                 {{-- اختيار الحالة --}}
                 <div class="mb-3">
                    <label for="status" class="form-label">الحالة <span class="text-danger">*</span></label>
                    <select name="attendance[0][status]" id="status" class="form-select @error('attendance.0.status') is-invalid @enderror" required>
                         @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" {{ old('attendance.0.status', 'Present') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('attendance.0.status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                 {{-- وقت الدخول --}}
                 <div class="mb-3">
                    <label for="check_in_time" class="form-label">وقت الدخول:</label>
                    <input type="time" id="check_in_time" name="attendance[0][check_in_time]" class="form-control @error('attendance.0.check_in_time') is-invalid @enderror" value="{{ old('attendance.0.check_in_time') }}">
                    @error('attendance.0.check_in_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- وقت الخروج --}}
                 <div class="mb-3">
                    <label for="check_out_time" class="form-label">وقت الخروج:</label>
                    <input type="time" id="check_out_time" name="attendance[0][check_out_time]" class="form-control @error('attendance.0.check_out_time') is-invalid @enderror" value="{{ old('attendance.0.check_out_time') }}">
                    @error('attendance.0.check_out_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- الملاحظات --}}
                 <div class="mb-3">
                    <label for="notes" class="form-label">ملاحظات:</label>
                   <textarea name="attendance[0][notes]" id="notes" rows="3" class="form-control @error('attendance.0.notes') is-invalid @enderror">{{ old('attendance.0.notes') }}</textarea>
                    @error('attendance.0.notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- أزرار الحفظ والإلغاء --}}
                <div class="mt-4 d-flex justify-content-end">
                     <a href="{{ route('admin.attendance.index', ['date' => $attendanceDate]) }}" class="btn btn-secondary me-2">إلغاء</a>
                    <button type="submit" class="btn btn-primary" {{ $childrenWithoutAttendance->isEmpty() ? 'disabled' : '' }}>
                         <i data-feather="save" class="me-1"></i> حفظ السجل
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection