@extends('layouts.admin')

@section('title', 'عرض الوسائط')

@section('header-buttons')
    <a href="{{ route('admin.media.edit', $medium) }}" class="btn btn-sm btn-warning me-2">
        <i data-feather="edit-2" class="me-1"></i> تعديل البيانات
    </a>
    <form action="{{ route('admin.media.destroy', $medium) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه الوسائط نهائيًا؟');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger me-2">
            <i data-feather="trash-2" class="me-1"></i> حذف
        </button>
    </form>
    <a href="{{ route('admin.media.index') }}" class="btn btn-sm btn-outline-secondary">
        <i data-feather="arrow-left" class="me-1"></i> العودة للمعرض
    </a>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-header">
             <h5 class="card-title mb-0">تفاصيل الوسائط</h5>
        </div>
         <div class="card-body">
            <div class="row">
                 <div class="col-md-6 text-center mb-3 mb-md-0">
                     @if($medium->media_type == 'Image')
                        <a href="{{ Storage::disk('public')->url($medium->file_path) }}" target="_blank">
                            <img src="{{ Storage::disk('public')->url($medium->file_path) }}" class="img-fluid rounded shadow-sm" alt="{{ $medium->description ?: 'صورة' }}" style="max-height: 400px;">
                        </a>
                    @elseif($medium->media_type == 'Video')
                        <video width="100%" style="max-height: 400px;" controls preload="metadata">
                            <source src="{{ Storage::disk('public')->url($medium->file_path) }}" type="{{ Storage::mimeType($medium->file_path) ?? 'video/mp4' }}">
                            متصفحك لا يدعم عرض الفيديو. <a href="{{ Storage::disk('public')->url($medium->file_path) }}" target="_blank">تحميل الفيديو</a>
                        </video>
                    @endif
                 </div>
                 <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-4">الوصف:</dt>
                        <dd class="col-sm-8">{{ $medium->description ?: '-' }}</dd>

                         <dt class="col-sm-4">نوع الملف:</dt>
                         <dd class="col-sm-8">{{ $medium->media_type }}</dd>

                         <dt class="col-sm-4">المسار:</dt>
                         <dd class="col-sm-8"><code class="small">{{ $medium->file_path }}</code></dd>

                         <dt class="col-sm-4">تاريخ الرفع:</dt>
                         <dd class="col-sm-8">{{ $medium->upload_date->format('Y-m-d H:i A') }}</dd>

                        <dt class="col-sm-4">رُفع بواسطة:</dt>
                         <dd class="col-sm-8">{{ $medium->uploader->name ?? 'N/A' }}</dd>

                         <dt class="col-sm-4">مرتبط بفصل:</dt>
                         <dd class="col-sm-8">{{ $medium->associatedClass->class_name ?? '-' }}</dd>

                        <dt class="col-sm-4">مرتبط بطفل:</dt>
                        <dd class="col-sm-8">{{ $medium->associatedChild->full_name ?? '-' }}</dd>

                         <dt class="col-sm-4">مرتبط بفعالية:</dt>
                        <dd class="col-sm-8">{{ $medium->associatedEvent->event_name ?? '-' }}</dd>

                    </dl>
                 </div>
            </div>
         </div>
    </div>
</div>
@endsection