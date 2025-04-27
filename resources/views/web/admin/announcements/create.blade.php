@extends('layouts.admin')

@section('title', 'إضافة إعلان جديد')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">إضافة إعلان جديد</h1>
         <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline-secondary">
            <i data-feather="arrow-right" class="me-1"></i> العودة إلى القائمة
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.announcements.store') }}" method="POST">
                @csrf

                {{-- حقل العنوان --}}
                <div class="mb-3">
                    <label for="title" class="form-label">عنوان الإعلان <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- حقل المحتوى (استخدام محرر نصي متقدم هنا سيكون أفضل - مثل CKEditor, TinyMCE) --}}
                <div class="mb-3">
                    <label for="content" class="form-label">محتوى الإعلان <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="8" required>{{ old('content') }}</textarea>
                    @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                     {{-- ملاحظة: لتكامل محرر نصي، قد تحتاج لإضافة JS و CSS خاص به --}}
                     {{-- مثال: <textarea id="editor" name="content"></textarea> --}}
                </div>

                {{-- حقل اختيار الفصل المستهدف --}}
                 <div class="mb-3">
                    <label for="target_class_id" class="form-label">الفصل المستهدف</label>
                    <select class="form-select @error('target_class_id') is-invalid @enderror" id="target_class_id" name="target_class_id">
                        <option value="">-- كل الفصول (إعلان عام) --</option>
                        @foreach($classes as $id => $name)
                            <option value="{{ $id }}" {{ old('target_class_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('target_class_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">اترك الحقل فارغًا لإرسال الإعلان لجميع أولياء الأمور.</small>
                </div>

                {{-- يمكنك إضافة حقل لتاريخ النشر المستقبلي إذا أردت --}}
                {{-- <div class="mb-3">
                    <label for="publish_at" class="form-label">تاريخ النشر (اختياري)</label>
                    <input type="datetime-local" class="form-control @error('publish_at') is-invalid @enderror" id="publish_at" name="publish_at" value="{{ old('publish_at') }}">
                     <small class="form-text text-muted">اتركه فارغًا للنشر فورًا.</small>
                     @error('publish_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div> --}}

                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline-secondary me-2">إلغاء</a>
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save" class="me-1"></i> حفظ ونشر الإعلان
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

{{-- لتضمين محرر نصي متقدم (مثال CKEditor - يحتاج إعداد وتثبيت) --}}
{{-- @push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/35.3.2/classic/ckeditor.js"></script>
<script>
    ClassicEditor
        .create( document.querySelector( '#content' ) ) // استهداف الـ textarea بواسطة ID
        .catch( error => {
            console.error( error );
        } );
</script>
@endpush --}}