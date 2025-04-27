@extends('layouts.supervisor') {{-- أو layouts.admin --}}

@section('title', 'تعديل المصدر التعليمي: ' . $resource->title)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">تعديل المصدر التعليمي</h1>
         {{-- رابط العودة لمسار المشرف --}}
         <a href="{{ route('supervisor.resources.index') }}" class="btn btn-outline-secondary">
            <i data-feather="arrow-right" class="me-1"></i> العودة إلى القائمة
        </a>
    </div>

    <div class="card">
        <div class="card-body">
             {{-- رابط الإرسال لمسار المشرف --}}
            <form action="{{ route('supervisor.resources.update', $resource) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- حقل العنوان --}}
                <div class="mb-3">
                    <label for="title" class="form-label">العنوان <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $resource->title) }}" required>
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- حقل الوصف --}}
                <div class="mb-3">
                    <label for="description" class="form-label">الوصف (اختياري)</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $resource->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row">
                    {{-- حقل نوع المصدر --}}
                    <div class="col-md-6 mb-3">
                        <label for="resource_type" class="form-label">نوع المصدر <span class="text-danger">*</span></label>
                        <select class="form-select @error('resource_type') is-invalid @enderror" id="resource_type" name="resource_type" required>
                            @if(isset($resourceTypes) && is_array($resourceTypes))
                                @foreach($resourceTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('resource_type', $resource->resource_type) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            @endif
                        </select>
                        @error('resource_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- حقل الرابط أو المسار --}}
                     <div class="col-md-6 mb-3">
                        <label for="url_or_path" class="form-label">الرابط (URL) أو المسار <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('url_or_path') is-invalid @enderror" id="url_or_path" name="url_or_path" value="{{ old('url_or_path', $resource->url_or_path) }}" required>
                        @error('url_or_path') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row">
                     {{-- حقل العمر الأدنى --}}
                    <div class="col-md-4 mb-3">
                        <label for="target_age_min" class="form-label">العمر الأدنى المستهدف (سنوات)</label>
                        <input type="number" class="form-control @error('target_age_min') is-invalid @enderror" id="target_age_min" name="target_age_min" value="{{ old('target_age_min', $resource->target_age_min) }}" min="0" max="18">
                        @error('target_age_min') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                     {{-- حقل العمر الأقصى --}}
                    <div class="col-md-4 mb-3">
                        <label for="target_age_max" class="form-label">العمر الأقصى المستهدف (سنوات)</label>
                        <input type="number" class="form-control @error('target_age_max') is-invalid @enderror" id="target_age_max" name="target_age_max" value="{{ old('target_age_max', $resource->target_age_max) }}" min="0" max="18">
                        @error('target_age_max') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    {{-- حقل الموضوع --}}
                    <div class="col-md-4 mb-3">
                        <label for="subject" class="form-label">الموضوع (اختياري)</label>
                        <input type="text" class="form-control @error('subject') is-invalid @enderror" id="subject" name="subject" value="{{ old('subject', $resource->subject) }}">
                        @error('subject') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>


                <div class="d-flex justify-content-end mt-4">
                    {{-- رابط الإلغاء لمسار المشرف --}}
                    <a href="{{ route('supervisor.resources.index') }}" class="btn btn-outline-secondary me-2">إلغاء</a>
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save" class="me-1"></i> حفظ التعديلات
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection