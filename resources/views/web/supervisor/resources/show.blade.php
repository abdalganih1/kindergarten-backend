@extends('layouts.supervisor') {{-- أو layouts.admin --}}

@section('title', 'تفاصيل المصدر: ' . ($resource->title ?? 'غير متوفر')) {{-- إضافة تحقق هنا أيضاً --}}

@section('header-buttons')
    {{-- التحقق المباشر من دور المستخدم لإظهار أزرار التعديل والحذف --}}
    {{-- يجب أن يكون $resource متاحًا هنا لأنه يتم تمريره من المتحكم --}}
    @if(Auth::check() && Auth::user()->role === 'Supervisor')
        {{-- يمكنك إضافة تحقق إضافي هنا لملكية المصدر إذا لزم الأمر --}}
        {{-- @if($resource->added_by_id === Auth::user()->adminProfile?->admin_id) --}}

            {{-- *** استخدام اسم البارامتر الصحيح 'educationalResource' وقيمة ID *** --}}
            <a href="{{ route('supervisor.resources.edit', ['educationalResource' => $resource->resource_id]) }}" class="btn btn-sm btn-warning me-2">
                <i data-feather="edit-2" class="me-1"></i> تعديل
            </a>

            {{-- *** استخدام اسم البارامتر الصحيح 'educationalResource' وقيمة ID *** --}}
            <form action="{{ route('supervisor.resources.destroy', ['educationalResource' => $resource->resource_id]) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا المصدر التعليمي؟');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger me-2">
                    <i data-feather="trash-2" class="me-1"></i> حذف
                </button>
            </form>
        {{-- @endif --}}
    @endif
    {{-- رابط العودة لمسار المشرف --}}
    <a href="{{ route('supervisor.resources.index') }}" class="btn btn-sm btn-outline-secondary">
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
             {{-- التحقق من وجود $resource قبل استخدامه --}}
             @if(isset($resource))
                 <h3 class="card-title border-bottom pb-2 mb-3">{{ $resource->title ?? 'عنوان غير متوفر' }}</h3>
                 <dl class="row mb-0">
                     <dt class="col-sm-3">الوصف:</dt>
                     <dd class="col-sm-9">{{ $resource->description ?: '-' }}</dd>

                     <dt class="col-sm-3">النوع:</dt>
                     <dd class="col-sm-9">
                        @php
                            // تعريف $resourceTypes هنا كاحتياطي إذا لم يتم تمريرها من المتحكم
                            $localResourceTypes = $resourceTypes ?? ['Video' => 'فيديو', 'Article' => 'مقالة', 'Game' => 'لعبة', 'Link' => 'رابط'];
                            $typeBadge = match($resource->resource_type ?? null) { 'Video' => 'danger', 'Article' => 'info', 'Game' => 'success', 'Link' => 'primary', default => 'secondary' };
                            $typeText = $localResourceTypes[$resource->resource_type] ?? $resource->resource_type ?? 'غير معروف';
                        @endphp
                        <span class="badge bg-{{ $typeBadge }} fs-6">{{ $typeText }}</span>
                    </dd>

                     <dt class="col-sm-3">الرابط/المسار:</dt>
                     <dd class="col-sm-9">
                         {{-- التحقق من وجود الرابط قبل عرضه --}}
                         @if($resource->url_or_path)
                            <a href="{{ $resource->url_or_path }}" target="_blank" title="{{ $resource->url_or_path }}">
                                {{ $resource->url_or_path }} <i data-feather="external-link" style="width:14px; height:14px;"></i>
                            </a>
                         @else
                            -
                         @endif
                     </dd>

                     <dt class="col-sm-3">العمر المستهدف:</dt>
                     <dd class="col-sm-9">
                        @if($resource->target_age_min && $resource->target_age_max) {{ $resource->target_age_min }} - {{ $resource->target_age_max }} سنوات
                        @elseif($resource->target_age_min) {{ $resource->target_age_min }}+ سنوات
                        @elseif($resource->target_age_max) حتى {{ $resource->target_age_max }} سنوات
                        @else<span class="text-muted">غير محدد</span>
                        @endif
                     </dd>

                     <dt class="col-sm-3">الموضوع:</dt>
                     <dd class="col-sm-9">{{ $resource->subject ?: '-' }}</dd>

                     <dt class="col-sm-3">أضيف بواسطة:</dt>
                     <dd class="col-sm-9">{{ $resource->addedByAdmin?->user?->name ?? 'N/A' }}</dd>

                     <dt class="col-sm-3">تاريخ الإضافة:</dt>
                     <dd class="col-sm-9">{{ $resource->created_at ? $resource->created_at->format('Y-m-d H:i A') : 'N/A' }}</dd>

                     <dt class="col-sm-3">آخر تحديث:</dt>
                     <dd class="col-sm-9">{{ $resource->updated_at ? $resource->updated_at->format('Y-m-d H:i A') : 'N/A' }}</dd>
                 </dl>
             @else
                 <p class="text-danger text-center">تعذر تحميل تفاصيل المصدر.</p>
             @endif
        </div>
    </div>
</div>
@endsection