@extends('layouts.admin')

@section('title', 'إضافة فصل دراسي جديد')

@section('content')
<div class="container-fluid">
     <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">إضافة فصل دراسي جديد</h1>
         <a href="{{ route('admin.classes.index') }}" class="btn btn-outline-secondary">
            <i data-feather="arrow-right" class="me-1"></i> العودة إلى القائمة
        </a>
    </div>

    <div class="card">
         <div class="card-body">
             <form action="{{ route('admin.classes.store') }}" method="POST">
                @csrf

                {{-- اسم الفصل --}}
                <div class="mb-3">
                    <label for="class_name" class="form-label">اسم الفصل <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('class_name') is-invalid @enderror" id="class_name" name="class_name" value="{{ old('class_name') }}" required placeholder="مثال: فصل النجوم، فصل الفراشات">
                    @error('class_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- الوصف --}}
                <div class="mb-3">
                    <label for="description" class="form-label">الوصف (اختياري)</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                 <div class="row">
                     {{-- العمر الأدنى --}}
                    <div class="col-md-6 mb-3">
                        <label for="min_age" class="form-label">العمر الأدنى (سنوات) (اختياري)</label>
                        <input type="number" class="form-control @error('min_age') is-invalid @enderror" id="min_age" name="min_age" value="{{ old('min_age') }}" min="0" max="18">
                        @error('min_age') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                     {{-- العمر الأقصى --}}
                    <div class="col-md-6 mb-3">
                        <label for="max_age" class="form-label">العمر الأقصى (سنوات) (اختياري)</label>
                        <input type="number" class="form-control @error('max_age') is-invalid @enderror" id="max_age" name="max_age" value="{{ old('max_age') }}" min="0" max="18">
                        @error('max_age') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                 </div>


                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('admin.classes.index') }}" class="btn btn-outline-secondary me-2">إلغاء</a>
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save" class="me-1"></i> حفظ الفصل
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection