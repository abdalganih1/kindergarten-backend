@extends('layouts.admin')

@section('title', 'إضافة طفل جديد')

@section('content')
<div class="container-fluid">
     <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">إضافة طفل جديد</h1>
         <a href="{{ route('admin.children.index') }}" class="btn btn-outline-secondary">
            <i data-feather="arrow-right" class="me-1"></i> العودة إلى القائمة
        </a>
    </div>

    <form action="{{ route('admin.children.store') }}" method="POST" enctype="multipart/form-data"> {{-- Add enctype for file uploads --}}
        @csrf
        <div class="row">
            {{-- Column 1: Basic Info & Class --}}
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">المعلومات الأساسية</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">الاسم الأول <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                                @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">اسم العائلة <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                                @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="row">
                             <div class="col-md-6 mb-3">
                                <label for="date_of_birth" class="form-label">تاريخ الميلاد <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" required max="{{ now()->format('Y-m-d') }}">
                                @error('date_of_birth') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="gender" class="form-label">الجنس</label>
                                <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender">
                                    <option value="">-- اختر --</option>
                                    <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>ذكر</option>
                                    <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>أنثى</option>
                                    <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>آخر</option>
                                </select>
                                @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                         <div class="row">
                             <div class="col-md-6 mb-3">
                                <label for="enrollment_date" class="form-label">تاريخ التسجيل <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('enrollment_date') is-invalid @enderror" id="enrollment_date" name="enrollment_date" value="{{ old('enrollment_date', now()->format('Y-m-d')) }}" required>
                                @error('enrollment_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="class_id" class="form-label">الفصل الدراسي</label>
                                <select class="form-select @error('class_id') is-invalid @enderror" id="class_id" name="class_id">
                                    <option value="">-- غير محدد --</option>
                                     @foreach($classes as $id => $name)
                                        <option value="{{ $id }}" {{ old('class_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('class_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                         <div class="mb-3">
                            <label for="allergies" class="form-label">الحساسية (إن وجدت)</label>
                            <textarea class="form-control @error('allergies') is-invalid @enderror" id="allergies" name="allergies" rows="3">{{ old('allergies') }}</textarea>
                             @error('allergies') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                         <div class="mb-3">
                            <label for="medical_notes" class="form-label">ملاحظات طبية إضافية</label>
                            <textarea class="form-control @error('medical_notes') is-invalid @enderror" id="medical_notes" name="medical_notes" rows="3">{{ old('medical_notes') }}</textarea>
                             @error('medical_notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Column 2: Photo & Parents --}}
            <div class="col-md-4">
                <div class="card mb-3">
                     <div class="card-header">صورة الطفل</div>
                     <div class="card-body text-center">
                         <img id="photoPreview" src="{{ asset('images/default-avatar.png') }}" alt="صورة الطفل" class="rounded mb-2" style="width: 150px; height: 150px; object-fit: cover;">
                         <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo" accept="image/png, image/jpeg, image/gif">
                          @error('photo') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                         <small class="form-text text-muted">اختياري. الصيغ المسموحة: JPG, PNG, GIF. الحجم الأقصى: 2MB.</small>
                     </div>
                </div>
                 <div class="card">
                     <div class="card-header">أولياء الأمور</div>
                     <div class="card-body">
                         <label for="parent_ids" class="form-label">اختر ولي أمر أو أكثر:</label>
                         <select class="form-select @error('parent_ids') is-invalid @enderror @error('parent_ids.*') is-invalid @enderror" id="parent_ids" name="parent_ids[]" multiple size="5"> {{-- Use select multiple --}}
                             {{-- <option value="" disabled>-- اختر --</option> --}}
                              @foreach($parents as $id => $name)
                                <option value="{{ $id }}" {{ in_array($id, old('parent_ids', [])) ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                         </select>
                          @error('parent_ids') <div class="invalid-feedback">{{ $message }}</div> @enderror
                          @error('parent_ids.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
                         <small class="form-text text-muted">يمكنك تحديد أكثر من ولي أمر بالضغط على Ctrl (أو Cmd) أثناء الاختيار.</small>
                     </div>
                </div>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-end">
            <a href="{{ route('admin.children.index') }}" class="btn btn-outline-secondary me-2">إلغاء</a>
            <button type="submit" class="btn btn-primary">
                <i data-feather="save" class="me-1"></i> حفظ ملف الطفل
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Preview photo before upload
    const photoInput = document.getElementById('photo');
    const photoPreview = document.getElementById('photoPreview');
    const defaultAvatar = "{{ asset('images/default-avatar.png') }}";

    photoInput.addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                photoPreview.src = e.target.result;
            }
            reader.readAsDataURL(file);
        } else {
             photoPreview.src = defaultAvatar; // Reset if no file selected
        }
    });
</script>
@endpush