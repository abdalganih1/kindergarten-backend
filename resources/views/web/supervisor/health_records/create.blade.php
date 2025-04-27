@extends('layouts.supervisor') {{-- أو layouts.admin --}}

@section('title', 'إضافة سجل صحي جديد')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">إضافة سجل صحي جديد</h1>
         <a href="{{ route('supervisor.health-records.index') }}" class="btn btn-outline-secondary">
            <i data-feather="arrow-right" class="me-1"></i> العودة إلى القائمة
        </a>
    </div>

    <div class="card">
        <div class="card-body">
             <form action="{{ route('supervisor.health-records.store') }}" method="POST" enctype="multipart/form-data"> {{-- مسار المشرف --}}
                @csrf

                {{-- حقل اختيار الطفل (فقط أطفال المشرف) --}}
                <div class="mb-3">
                    <label for="child_id" class="form-label">الطفل <span class="text-danger">*</span></label>
                    <select class="form-select @error('child_id') is-invalid @enderror" id="child_id" name="child_id" required>
                        <option value="" disabled {{ old('child_id', $selectedChildId) ? '' : 'selected' }}>-- اختر الطفل --</option>
                        @foreach($children as $id => $name) {{-- $children هنا تحتوي أطفال المشرف --}}
                            <option value="{{ $id }}" {{ old('child_id', $selectedChildId) == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('child_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                 {{-- بقية حقول النموذج مطابقة لنموذج المدير --}}
                 <div class="row">
                    {{-- حقل نوع السجل --}}
                    <div class="col-md-6 mb-3">
                        <label for="record_type" class="form-label">نوع السجل <span class="text-danger">*</span></label>
                        <select class="form-select @error('record_type') is-invalid @enderror" id="record_type" name="record_type" required>
                            <option value="" disabled {{ old('record_type') ? '' : 'selected' }}>-- اختر النوع --</option>
                            @foreach($recordTypes as $value => $label) <option value="{{ $value }}" {{ old('record_type') == $value ? 'selected' : '' }}>{{ $label }}</option> @endforeach
                        </select>
                        @error('record_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    {{-- حقل تاريخ السجل --}}
                    <div class="col-md-6 mb-3">
                        <label for="record_date" class="form-label">تاريخ السجل <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('record_date') is-invalid @enderror" id="record_date" name="record_date" value="{{ old('record_date', now()->format('Y-m-d')) }}" required max="{{ now()->format('Y-m-d') }}">
                        @error('record_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                 </div>
                 {{-- حقل التفاصيل --}}
                 <div class="mb-3">
                    <label for="details" class="form-label">التفاصيل <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('details') is-invalid @enderror" id="details" name="details" rows="4" required placeholder="...">{{ old('details') }}</textarea>
                    @error('details') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="row">
                     {{-- حقل تاريخ الاستحقاق القادم --}}
                    <div class="col-md-6 mb-3">
                        <label for="next_due_date" class="form-label">تاريخ الاستحقاق/المتابعة القادم (اختياري)</label>
                        <input type="date" class="form-control @error('next_due_date') is-invalid @enderror" id="next_due_date" name="next_due_date" value="{{ old('next_due_date') }}" min="{{ now()->format('Y-m-d') }}">
                        @error('next_due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    {{-- حقل رفع مستند --}}
                    <div class="col-md-6 mb-3">
                         <label for="document" class="form-label">مستند مرفق (اختياري)</label>
                         <input class="form-control @error('document') is-invalid @enderror" type="file" id="document" name="document" accept="image/jpeg,image/png,application/pdf">
                         @error('document') <div class="invalid-feedback">{{ $message }}</div> @enderror
                         <small class="form-text text-muted">ملفات PDF, JPG, PNG مسموحة (حد أقصى 5MB).</small>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('supervisor.health-records.index') }}" class="btn btn-outline-secondary me-2">إلغاء</a>
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save" class="me-1"></i> حفظ السجل الصحي
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection