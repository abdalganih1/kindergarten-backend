@extends('layouts.admin')

@section('title', 'تعديل الفعالية: ' . $event->event_name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">تعديل الفعالية / الرحلة</h1>
         <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary">
            <i data-feather="arrow-right" class="me-1"></i> العودة إلى القائمة
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.events.update', $event) }}" method="POST">
                @csrf
                @method('PUT')

                 {{-- اسم الفعالية --}}
                 <div class="mb-3">
                    <label for="event_name" class="form-label">اسم الفعالية / الرحلة <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('event_name') is-invalid @enderror" id="event_name" name="event_name" value="{{ old('event_name', $event->event_name) }}" required>
                    @error('event_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                 {{-- وصف الفعالية --}}
                 <div class="mb-3">
                    <label for="description" class="form-label">الوصف (اختياري)</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $event->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row">
                     {{-- تاريخ ووقت الفعالية --}}
                    <div class="col-md-6 mb-3">
                        <label for="event_date" class="form-label">تاريخ ووقت الفعالية <span class="text-danger">*</span></label>
                        {{-- استخدام event_date_form المهيأ في المتحكم --}}
                        <input type="datetime-local" class="form-control @error('event_date') is-invalid @enderror" id="event_date" name="event_date" value="{{ old('event_date', $event->event_date_form ?? '') }}" required>
                        @error('event_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- موقع الفعالية --}}
                    <div class="col-md-6 mb-3">
                        <label for="location" class="form-label">الموقع (اختياري)</label>
                        <input type="text" class="form-control @error('location') is-invalid @enderror" id="location" name="location" value="{{ old('location', $event->location) }}">
                         @error('location') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                 {{-- إعدادات التسجيل --}}
                <div class="border p-3 rounded mb-3 bg-light">
                    <div class="form-check form-switch mb-3">
                      <input class="form-check-input" type="checkbox" role="switch" id="requires_registration" name="requires_registration" value="1" {{ old('requires_registration', $event->requires_registration) ? 'checked' : '' }} onchange="toggleRegistrationDeadline(this.checked)">
                      <label class="form-check-label" for="requires_registration">هذه الفعالية تتطلب تسجيل مسبق؟</label>
                    </div>

                    {{-- الموعد النهائي للتسجيل --}}
                    <div id="registrationDeadlineGroup" style="{{ old('requires_registration', $event->requires_registration) ? '' : 'display: none;' }}">
                        <label for="registration_deadline" class="form-label">الموعد النهائي للتسجيل</label>
                        {{-- استخدام registration_deadline_form المهيأ في المتحكم --}}
                        <input type="datetime-local" class="form-control @error('registration_deadline') is-invalid @enderror" id="registration_deadline" name="registration_deadline" value="{{ old('registration_deadline', $event->registration_deadline_form ?? '') }}">
                         @error('registration_deadline') <div class="invalid-feedback">{{ $message }}</div> @enderror
                         <small class="form-text text-muted">يجب أن يكون قبل تاريخ ووقت الفعالية.</small>
                    </div>
                </div>


                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary me-2">إلغاء</a>
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save" class="me-1"></i> حفظ التعديلات
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

{{-- تضمين نفس دالة JS لتبديل حقل الموعد النهائي --}}
@push('scripts')
<script>
    function toggleRegistrationDeadline(isChecked) {
        const deadlineGroup = document.getElementById('registrationDeadlineGroup');
        const deadlineInput = document.getElementById('registration_deadline');
        if (isChecked) {
            deadlineGroup.style.display = 'block';
            deadlineInput.required = true;
        } else {
            deadlineGroup.style.display = 'none';
            deadlineInput.required = false;
            deadlineInput.value = '';
        }
    }
    // التشغيل الأولي
    toggleRegistrationDeadline(document.getElementById('requires_registration').checked);
</script>
@endpush