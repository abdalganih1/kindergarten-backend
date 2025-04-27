@extends('layouts.admin')

@section('title', 'تعديل الإعلان: ' . $announcement->title)

@section('content')
<div class="container-fluid">
     <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">تعديل الإعلان</h1>
         <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline-secondary">
            <i data-feather="arrow-right" class="me-1"></i> العودة إلى القائمة
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.announcements.update', $announcement) }}" method="POST">
                @csrf
                @method('PUT') {{-- استخدام PUT للتحديث --}}

                {{-- حقل العنوان --}}
                <div class="mb-3">
                    <label for="title" class="form-label">عنوان الإعلان <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $announcement->title) }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- حقل المحتوى --}}
                <div class="mb-3">
                    <label for="content" class="form-label">محتوى الإعلان <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="8" required>{{ old('content', $announcement->content) }}</textarea>
                    @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- حقل اختيار الفصل المستهدف --}}
                 <div class="mb-3">
                    <label for="target_class_id" class="form-label">الفصل المستهدف</label>
                    <select class="form-select @error('target_class_id') is-invalid @enderror" id="target_class_id" name="target_class_id">
                        <option value="">-- كل الفصول (إعلان عام) --</option>
                        @foreach($classes as $id => $name)
                            <option value="{{ $id }}" {{ old('target_class_id', $announcement->target_class_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('target_class_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">اترك الحقل فارغًا لإرسال الإعلان لجميع أولياء الأمور.</small>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline-secondary me-2">إلغاء</a>
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save" class="me-1"></i> حفظ التعديلات
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

{{-- يمكنك إضافة نفس كود JS لمحرر النصوص هنا إذا أردت استخدامه في التعديل أيضاً --}}
{{-- @push('scripts') ... @endpush --}}