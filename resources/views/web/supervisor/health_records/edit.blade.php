@extends('layouts.supervisor') {{-- أو layouts.admin --}}

@section('title', 'تعديل السجل الصحي')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">تعديل السجل الصحي للطفل: <span class="text-primary">{{ $healthRecord->child->full_name }}</span></h1>
         <a href="{{ route('supervisor.health-records.index', ['child_id' => $healthRecord->child_id]) }}" class="btn btn-outline-secondary">
            <i data-feather="arrow-right" class="me-1"></i> العودة إلى السجلات
        </a>
    </div>

    <div class="card">
        <div class="card-body">
             <form action="{{ route('supervisor.health-records.update', $healthRecord) }}" method="POST" enctype="multipart/form-data"> {{-- مسار المشرف --}}
                @csrf
                @method('PUT')

                 {{-- عرض اسم الطفل (غير قابل للتعديل) --}}
                 <div class="mb-3">
                    <label class="form-label">الطفل:</label>
                     <input type="text" readonly class="form-control-plaintext" value="{{ $healthRecord->child->full_name }}">
                </div>

                 {{-- بقية حقول النموذج مطابقة لنموذج تعديل المدير --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="record_type" class="form-label">نوع السجل <span class="text-danger">*</span></label>
                        <select class="form-select @error('record_type') is-invalid @enderror" id="record_type" name="record_type" required>
                            @foreach($recordTypes as $value => $label)<option value="{{ $value }}" {{ old('record_type', $healthRecord->record_type) == $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach
                        </select>
                        @error('record_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="record_date" class="form-label">تاريخ السجل <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('record_date') is-invalid @enderror" id="record_date" name="record_date" value="{{ old('record_date', $healthRecord->record_date) }}" required max="{{ now()->format('Y-m-d') }}">
                        @error('record_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                 </div>
                 <div class="mb-3">
                    <label for="details" class="form-label">التفاصيل <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('details') is-invalid @enderror" id="details" name="details" rows="4" required>{{ old('details', $healthRecord->details) }}</textarea>
                    @error('details')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="next_due_date" class="form-label">تاريخ الاستحقاق/المتابعة القادم (اختياري)</label>
                        <input type="date" class="form-control @error('next_due_date') is-invalid @enderror" id="next_due_date" name="next_due_date" value="{{ old('next_due_date', $healthRecord->next_due_date) }}" min="{{ now()->format('Y-m-d') }}">
                        @error('next_due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                         <label for="document" class="form-label">تغيير المستند المرفق (اختياري)</label>
                         <input class="form-control @error('document') is-invalid @enderror" type="file" id="document" name="document" accept="image/jpeg,image/png,application/pdf">
                         @error('document')<div class="invalid-feedback">{{ $message }}</div>@enderror
                         @if($healthRecord->document_path && Storage::disk('public')->exists($healthRecord->document_path))
                            <div class="mt-2"> المستند الحالي: <a href="{{ Storage::disk('public')->url($healthRecord->document_path) }}" target="_blank"><i data-feather="file-text"></i> {{ basename($healthRecord->document_path) }}</a>
                                <div class="form-check form-check-inline ms-3"><input class="form-check-input" type="checkbox" name="remove_document" id="remove_document" value="1"><label class="form-check-label small text-danger" for="remove_document">إزالة المستند</label></div>
                            </div>
                         @endif
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('supervisor.health-records.index', ['child_id' => $healthRecord->child_id]) }}" class="btn btn-outline-secondary me-2">إلغاء</a> {{-- مسار المشرف --}}
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save" class="me-1"></i> حفظ التعديلات
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection