@extends('layouts.admin')

@section('title', 'تفاصيل المصدر: ' . $educationalResource->title)

@section('header-buttons')
    {{-- === تعديل هنا === --}}
    <a href="{{ route('admin.resources.edit', ['educationalResource' => $educationalResource]) }}" class="btn btn-sm btn-warning me-2">
        <i data-feather="edit-2" class="me-1"></i> تعديل
    </a>
     {{-- === تعديل هنا === --}}
     <form action="{{ route('admin.resources.destroy', ['educationalResource' => $educationalResource]) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا المصدر التعليمي؟');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger me-2">
            <i data-feather="trash-2" class="me-1"></i> حذف
        </button>
    </form>
    <a href="{{ route('admin.resources.index') }}" class="btn btn-sm btn-outline-secondary">
        <i data-feather="arrow-left" class="me-1"></i> العودة إلى القائمة
    </a>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">تفاصيل المصدر التعليمي</h5>
        </div>
        <div class="card-body">
             <h3 class="card-title border-bottom pb-2 mb-3">{{ $educationalResource->title }}</h3>
             <dl class="row mb-0">
                 <dt class="col-sm-3">الوصف:</dt>
                 <dd class="col-sm-9">{{ $educationalResource->description ?: '-' }}</dd>

                <dt class="col-sm-3">النوع:</dt>
                <dd class="col-sm-9">
                    @php
                        $typeBadge = match($educationalResource->resource_type) { 'Video' => 'danger', 'Article' => 'info', 'Game' => 'success', 'Link' => 'primary', default => 'secondary' };
                        $typeText = match($educationalResource->resource_type) { 'Video' => 'فيديو', 'Article' => 'مقالة', 'Game' => 'لعبة', 'Link' => 'رابط', default => $educationalResource->resource_type };
                    @endphp
                    <span class="badge bg-{{ $typeBadge }} fs-6">{{ $typeText }}</span>
                </dd>

                 <dt class="col-sm-3">الرابط/المسار:</dt>
                <dd class="col-sm-9">
                     <a href="{{ $educationalResource->url_or_path }}" target="_blank">{{ $educationalResource->url_or_path }} <i data-feather="external-link" style="width:14px; height:14px;"></i></a>
                 </dd>

                 <dt class="col-sm-3">العمر المستهدف:</dt>
                <dd class="col-sm-9">
                    @if($educationalResource->target_age_min && $educationalResource->target_age_max)
                        {{ $educationalResource->target_age_min }} - {{ $educationalResource->target_age_max }} سنوات
                    @elseif($educationalResource->target_age_min)
                        {{ $educationalResource->target_age_min }}+ سنوات
                    @elseif($educationalResource->target_age_max)
                        حتى {{ $educationalResource->target_age_max }} سنوات
                    @else
                        <span class="text-muted">غير محدد</span>
                    @endif
                </dd>

                <dt class="col-sm-3">الموضوع:</dt>
                <dd class="col-sm-9">{{ $educationalResource->subject ?: '-' }}</dd>

                 <dt class="col-sm-3">أضيف بواسطة:</dt>
                 <dd class="col-sm-9">{{ $educationalResource->addedByAdmin->user->name ?? 'N/A' }}</dd>

                 <dt class="col-sm-3">تاريخ الإضافة:</dt>
                 <dd class="col-sm-9">{{ $educationalResource->added_at ? $educationalResource->added_at->format('Y-m-d H:i A') : $educationalResource->created_at->format('Y-m-d H:i A') }}</dd>

                 <dt class="col-sm-3">آخر تحديث:</dt>
                 <dd class="col-sm-9">{{ $educationalResource->updated_at->format('Y-m-d H:i A') }}</dd>
             </dl>
        </div>
    </div>
</div>
@endsection