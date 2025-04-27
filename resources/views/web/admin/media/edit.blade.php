@extends('layouts.admin')

@section('title', 'تعديل بيانات الوسائط')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">تعديل بيانات الوسائط</h1>
         <a href="{{ route('admin.media.index') }}" class="btn btn-outline-secondary">
            <i data-feather="arrow-right" class="me-1"></i> العودة إلى المعرض
        </a>
    </div>

     <div class="card">
        <div class="card-body">
            <div class="row">
                {{-- عرض الصورة المصغرة أو أيقونة الفيديو --}}
                <div class="col-md-4 text-center mb-3 mb-md-0">
                     @if($medium->media_type == 'Image')
                        <img src="{{ Storage::disk('public')->url($medium->file_path) }}" class="img-fluid rounded" alt="{{ $medium->description ?: 'صورة' }}" style="max-height: 250px;">
                    @elseif($medium->media_type == 'Video')
                        <div class="p-5 bg-dark rounded text-white">
                            <i data-feather="video" style="width: 80px; height: 80px;"></i>
                            <p class="mt-2">ملف فيديو</p>
                            <a href="{{ Storage::disk('public')->url($medium->file_path) }}" target="_blank" class="btn btn-sm btn-outline-light mt-2">مشاهدة</a>
                        </div>
                    @endif
                    <p class="mt-2 text-muted small"> المسار: {{ $medium->file_path }}</p>
                </div>

                {{-- نموذج تعديل البيانات --}}
                <div class="col-md-8">
                     <form action="{{ route('admin.media.update', $medium) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- حقل الوصف --}}
                        <div class="mb-3">
                            <label for="description" class="form-label">الوصف (اختياري)</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $medium->description) }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <h5 class="mt-4 mb-3 border-top pt-3">تعديل الربط (اختياري)</h5>

                        <div class="row">
                            {{-- الربط بفصل --}}
                            <div class="col-md-4 mb-3">
                                <label for="associated_class_id" class="form-label">ربط بفصل:</label>
                                <select class="form-select @error('associated_class_id') is-invalid @enderror" id="associated_class_id" name="associated_class_id">
                                    <option value="">-- لا يوجد ربط بفصل --</option>
                                    @foreach($classes as $id => $name)
                                        <option value="{{ $id }}" {{ old('associated_class_id', $medium->associated_class_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('associated_class_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- الربط بطفل --}}
                             <div class="col-md-4 mb-3">
                                <label for="associated_child_id" class="form-label">ربط بطفل:</label>
                                <select class="form-select @error('associated_child_id') is-invalid @enderror" id="associated_child_id" name="associated_child_id">
                                     <option value="">-- لا يوجد ربط بطفل --</option>
                                    @foreach($children as $child)
                                        <option value="{{ $child->child_id }}" {{ old('associated_child_id', $medium->associated_child_id) == $child->child_id ? 'selected' : '' }}>{{ $child->full_name }}</option>
                                    @endforeach
                                </select>
                                 @error('associated_child_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- الربط بفعالية --}}
                            <div class="col-md-4 mb-3">
                                <label for="associated_event_id" class="form-label">ربط بفعالية:</label>
                                <select class="form-select @error('associated_event_id') is-invalid @enderror" id="associated_event_id" name="associated_event_id">
                                    <option value="">-- لا يوجد ربط بفعالية --</option>
                                     @foreach($events as $id => $name)
                                        <option value="{{ $id }}" {{ old('associated_event_id', $medium->associated_event_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                 @error('associated_event_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>


                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('admin.media.index') }}" class="btn btn-outline-secondary me-2">إلغاء</a>
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="save" class="me-1"></i> حفظ التعديلات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection