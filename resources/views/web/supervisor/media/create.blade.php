@extends('layouts.supervisor') {{-- أو layouts.admin --}}

@section('title', 'رفع وسائط جديدة')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">رفع صور أو فيديو</h1>
         <a href="{{ route('supervisor.media.index') }}" class="btn btn-outline-secondary"> {{-- مسار المشرف --}}
            <i data-feather="arrow-right" class="me-1"></i> العودة إلى المعرض
        </a>
    </div>

     <div class="card">
        <div class="card-body">
             <form action="{{ route('supervisor.media.store') }}" method="POST" enctype="multipart/form-data"> {{-- مسار المشرف --}}
                @csrf

                {{-- حقل رفع الملفات --}}
                <div class="mb-3">
                    <label for="media_files" class="form-label">اختر الملفات (صور أو فيديو) <span class="text-danger">*</span></label>
                    <input class="form-control @error('media_files') is-invalid @enderror @error('media_files.*') is-invalid @enderror" type="file" id="media_files" name="media_files[]" multiple required accept="image/jpeg,image/png,image/gif,video/mp4,video/quicktime,video/avi,video/wmv">
                    @error('media_files') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @error('media_files.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="form-text text-muted">يمكنك تحديد أكثر من ملف. الأنواع المسموحة: JPG, PNG, GIF, MP4, MOV, AVI, WMV. الحد الأقصى للحجم: 20MB (مثال).</small>
                    <div id="file-list" class="mt-2 small"></div>
                </div>

                {{-- حقل الوصف --}}
                <div class="mb-3">
                    <label for="description" class="form-label">الوصف (اختياري)</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="سيتم تطبيق هذا الوصف على جميع الملفات المرفوعة في هذه المرة">{{ old('description') }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <h5 class="mt-4 mb-3 border-top pt-3">ربط الوسائط (اختياري - مقيد بفصولك)</h5>
                 <small class="form-text text-muted mb-3 d-block">يمكنك ربط هذه الوسائط بطفل أو فصل (تشرف عليه) أو فعالية.</small>

                 <div class="row">
                     {{-- الربط بفصل (فصول المشرف فقط) --}}
                    <div class="col-md-4 mb-3">
                        <label for="associated_class_id" class="form-label">ربط بفصل:</label>
                        <select class="form-select @error('associated_class_id') is-invalid @enderror" id="associated_class_id" name="associated_class_id">
                            <option value="">-- لا يوجد ربط بفصل --</option>
                            @foreach($classes as $id => $name) {{-- $classes هنا تحتوي فقط فصول المشرف --}}
                                <option value="{{ $id }}" {{ old('associated_class_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('associated_class_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- الربط بطفل (أطفال المشرف فقط) --}}
                     <div class="col-md-4 mb-3">
                        <label for="associated_child_id" class="form-label">ربط بطفل:</label>
                        <select class="form-select @error('associated_child_id') is-invalid @enderror" id="associated_child_id" name="associated_child_id">
                             <option value="">-- لا يوجد ربط بطفل --</option>
                            @foreach($children as $child) {{-- $children هنا تحتوي فقط أطفال فصول المشرف --}}
                                <option value="{{ $child->child_id }}" {{ old('associated_child_id') == $child->child_id ? 'selected' : '' }}>{{ $child->full_name }}</option>
                            @endforeach
                        </select>
                         @error('associated_child_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- الربط بفعالية (كل الفعاليات القادمة) --}}
                    <div class="col-md-4 mb-3">
                        <label for="associated_event_id" class="form-label">ربط بفعالية:</label>
                        <select class="form-select @error('associated_event_id') is-invalid @enderror" id="associated_event_id" name="associated_event_id">
                            <option value="">-- لا يوجد ربط بفعالية --</option>
                             @foreach($events as $id => $name)
                                <option value="{{ $id }}" {{ old('associated_event_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                         @error('associated_event_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('supervisor.media.index') }}" class="btn btn-outline-secondary me-2">إلغاء</a> {{-- مسار المشرف --}}
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="upload-cloud" class="me-1"></i> رفع الملفات
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- نفس سكريبت عرض قائمة الملفات المختارة --}}
<script>
    const fileInput = document.getElementById('media_files');
    const fileListDiv = document.getElementById('file-list');
    fileInput.addEventListener('change', function(event) { /* ... */ });
</script>
@endpush