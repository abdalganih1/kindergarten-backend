@extends('layouts.admin')

@section('title', 'تفاصيل الإعلان: ' . $announcement->title)

@section('header-buttons')
    <a href="{{ route('admin.announcements.edit', $announcement) }}" class="btn btn-sm btn-warning me-2">
        <i data-feather="edit-2" class="me-1"></i> تعديل
    </a>
     <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا الإعلان؟');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger me-2">
            <i data-feather="trash-2" class="me-1"></i> حذف
        </button>
    </form>
    <a href="{{ route('admin.announcements.index') }}" class="btn btn-sm btn-outline-secondary">
        <i data-feather="arrow-left" class="me-1"></i> العودة إلى القائمة
    </a>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">تفاصيل الإعلان</h5>
        </div>
        <div class="card-body">
            <h3 class="card-title border-bottom pb-2 mb-3">{{ $announcement->title }}</h3>

             <dl class="row mb-0">
                <dt class="col-sm-3">المؤلف:</dt>
                <dd class="col-sm-9">{{ $announcement->author->user->name ?? 'N/A' }}</dd>

                <dt class="col-sm-3">تاريخ النشر:</dt>
                <dd class="col-sm-9">{{ $announcement->publish_date ? $announcement->publish_date->format('Y-m-d H:i A') : $announcement->created_at->format('Y-m-d H:i A') }}</dd>

                <dt class="col-sm-3">الفصل المستهدف:</dt>
                <dd class="col-sm-9">
                     @if($announcement->targetClass)
                        <span class="badge bg-info fs-6">{{ $announcement->targetClass->class_name }}</span>
                    @else
                        <span class="badge bg-secondary fs-6">كل الفصول (إعلان عام)</span>
                    @endif
                </dd>

                <dt class="col-sm-3">تاريخ الإنشاء:</dt>
                <dd class="col-sm-9">{{ $announcement->created_at->format('Y-m-d H:i A') }}</dd>

                <dt class="col-sm-3">آخر تحديث:</dt>
                <dd class="col-sm-9">{{ $announcement->updated_at->format('Y-m-d H:i A') }}</dd>

                <dt class="col-sm-12 mt-3">محتوى الإعلان:</dt>
                <dd class="col-sm-12">
                    <div class="p-3 bg-light border rounded">
                         {{-- استخدام {!! !!} إذا كان المحتوى قد يحتوي على HTML من محرر نصي --}}
                         {{-- احذر من XSS إذا كان المحتوى من مصدر غير موثوق --}}
                        {!! nl2br(e($announcement->content)) !!} {{-- nl2br للحفاظ على فواصل الأسطر و e للهروب من HTML --}}
                    </div>
                </dd>
            </dl>
        </div>
    </div>

    {{-- يمكنك إضافة قسم لعرض المستخدمين الذين تم إرسال الإشعار لهم إذا لزم الأمر --}}

</div>
@endsection