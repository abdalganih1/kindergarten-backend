@extends('layouts.admin')

@section('title', 'تعديل سجل الحضور - ' . $attendance->child->full_name)

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">تعديل سجل الحضور</h2>

    <div class="card">
        <div class="card-header">
            تعديل سجل ليوم: {{ \Carbon\Carbon::parse($attendance->attendance_date)->format('Y-m-d') }} - الطفل: {{ $attendance->child->full_name }}
        </div>
        <div class="card-body">
            <form action="{{ route('admin.attendance.update', $attendance->attendance_id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- عرض اسم الطفل والتاريخ (غير قابل للتعديل هنا) --}}
                <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label">الطفل:</label>
                    <div class="col-sm-9">
                        <input type="text" readonly class="form-control-plaintext" value="{{ $attendance->child->full_name }}">
                    </div>
                </div>
                 <div class="mb-3 row">
                    <label class="col-sm-3 col-form-label">التاريخ:</label>
                    <div class="col-sm-9">
                        <input type="text" readonly class="form-control-plaintext" value="{{ \Carbon\Carbon::parse($attendance->attendance_date)->format('Y-m-d') }}">
                    </div>
                </div>

                {{-- حقل تعديل الحالة --}}
                <div class="mb-3 row">
                    <label for="status" class="col-sm-3 col-form-label">الحالة <span class="text-danger">*</span></label>
                    <div class="col-sm-9">
                        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                             @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" {{ old('status', $attendance->status) == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                 {{-- حقل تعديل وقت الدخول --}}
                <div class="mb-3 row">
                    <label for="check_in_time" class="col-sm-3 col-form-label">وقت الدخول:</label>
                    <div class="col-sm-9">
                        <input type="time" id="check_in_time" name="check_in_time" class="form-control @error('check_in_time') is-invalid @enderror" value="{{ old('check_in_time', $attendance->check_in_time) }}">
                         @error('check_in_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- حقل تعديل وقت الخروج --}}
                 <div class="mb-3 row">
                    <label for="check_out_time" class="col-sm-3 col-form-label">وقت الخروج:</label>
                    <div class="col-sm-9">
                        <input type="time" id="check_out_time" name="check_out_time" class="form-control @error('check_out_time') is-invalid @enderror" value="{{ old('check_out_time', $attendance->check_out_time) }}">
                         @error('check_out_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- حقل تعديل الملاحظات --}}
                 <div class="mb-3 row">
                    <label for="notes" class="col-sm-3 col-form-label">ملاحظات:</label>
                    <div class="col-sm-9">
                       <textarea name="notes" id="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $attendance->notes) }}</textarea>
                         @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- أزرار الحفظ والإلغاء --}}
                <div class="mt-4 d-flex justify-content-end">
                     <a href="{{ route('admin.attendance.index', ['date' => $attendance->attendance_date]) }}" class="btn btn-secondary me-2">إلغاء</a>
                    <button type="submit" class="btn btn-primary">
                         <i data-feather="save" class="me-1"></i> حفظ التعديلات
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection