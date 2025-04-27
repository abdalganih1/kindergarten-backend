@extends('layouts.admin')

@section('title', 'تعديل نشاط في الجدول')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">تعديل نشاط في الجدول</h1>
         <a href="{{ route('admin.schedules.index', ['class_id' => $weeklySchedule->class_id, 'day_of_week' => $weeklySchedule->day_of_week]) }}" class="btn btn-outline-secondary">
            <i data-feather="arrow-right" class="me-1"></i> العودة إلى الجدول
        </a>
    </div>

     <div class="card">
         <div class="card-body">
             <form action="{{ route('admin.schedules.update', $weeklySchedule) }}" method="POST">
                @csrf
                @method('PUT')

                 {{-- حقل الفصل الدراسي --}}
                 <div class="mb-3">
                    <label for="class_id" class="form-label">الفصل الدراسي <span class="text-danger">*</span></label>
                    <select class="form-select @error('class_id') is-invalid @enderror" id="class_id" name="class_id" required>
                        {{-- <option value="" disabled>-- اختر الفصل --</option> --}}
                         @foreach($classes as $id => $name)
                            <option value="{{ $id }}" {{ old('class_id', $weeklySchedule->class_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('class_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- حقل يوم الأسبوع --}}
                 <div class="mb-3">
                    <label for="day_of_week" class="form-label">يوم الأسبوع <span class="text-danger">*</span></label>
                    <select class="form-select @error('day_of_week') is-invalid @enderror" id="day_of_week" name="day_of_week" required>
                         {{-- <option value="" disabled>-- اختر اليوم --</option> --}}
                         @foreach($daysOfWeek as $value => $label)
                            <option value="{{ $value }}" {{ old('day_of_week', $weeklySchedule->day_of_week) == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('day_of_week') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row">
                     {{-- حقل وقت البدء --}}
                    <div class="col-md-6 mb-3">
                        <label for="start_time" class="form-label">وقت البدء <span class="text-danger">*</span></label>
                        <input type="time" class="form-control @error('start_time') is-invalid @enderror" id="start_time" name="start_time" value="{{ old('start_time', \Carbon\Carbon::parse($weeklySchedule->start_time)->format('H:i')) }}" required>
                        @error('start_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                     {{-- حقل وقت الانتهاء --}}
                    <div class="col-md-6 mb-3">
                         <label for="end_time" class="form-label">وقت الانتهاء <span class="text-danger">*</span></label>
                        <input type="time" class="form-control @error('end_time') is-invalid @enderror" id="end_time" name="end_time" value="{{ old('end_time', \Carbon\Carbon::parse($weeklySchedule->end_time)->format('H:i')) }}" required>
                        @error('end_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                 {{-- حقل وصف النشاط --}}
                 <div class="mb-3">
                    <label for="activity_description" class="form-label">وصف النشاط <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('activity_description') is-invalid @enderror" id="activity_description" name="activity_description" value="{{ old('activity_description', $weeklySchedule->activity_description) }}" required>
                    @error('activity_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>


                <div class="d-flex justify-content-end mt-4">
                     <a href="{{ route('admin.schedules.index', ['class_id' => $weeklySchedule->class_id, 'day_of_week' => $weeklySchedule->day_of_week]) }}" class="btn btn-outline-secondary me-2">إلغاء</a>
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save" class="me-1"></i> حفظ التعديلات
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection