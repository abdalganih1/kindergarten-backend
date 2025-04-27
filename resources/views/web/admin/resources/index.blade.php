@extends('layouts.admin')

@section('title', 'إدارة المصادر التعليمية')

@section('header-buttons')
<a href="{{ route('admin.resources.create') }}" class="btn btn-sm btn-success">
    <i data-feather="plus-circle" class="me-1"></i> إضافة مصدر جديد
</a>
@endsection

@section('content')
<div class="container-fluid">

    {{-- قسم الفلترة والبحث --}}
    <div class="card mb-4">
        <div class="card-header">
           <i data-feather="filter" class="me-1"></i> فلترة وبحث المصادر
        </div>
        <div class="card-body">
             <form method="GET" action="{{ route('admin.resources.index') }}" class="row g-3 align-items-end">
                {{-- حقل البحث --}}
                <div class="col-md-4">
                    <label for="search" class="form-label">بحث بالعنوان/الوصف:</label>
                    <input type="text" class="form-control form-control-sm" id="search" name="search" placeholder="كلمة مفتاحية..." value="{{ $searchTerm ?? '' }}">
                </div>

                {{-- فلتر نوع المصدر --}}
                <div class="col-md-3">
                    <label for="resource_type" class="form-label">نوع المصدر:</label>
                    <select class="form-select form-select-sm" id="resource_type" name="resource_type">
                        <option value="">-- الكل --</option>
                        @foreach($resourceTypes as $value => $label)
                            <option value="{{ $value }}" {{ $resourceType == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- فلتر الموضوع --}}
                 <div class="col-md-3">
                    <label for="subject" class="form-label">الموضوع:</label>
                    <input type="text" class="form-control form-control-sm" id="subject" name="subject" placeholder="مثل: لغة عربية..." value="{{ $subject ?? '' }}">
                </div>

                {{-- زر تطبيق الفلتر --}}
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">بحث</button>
                </div>
            </form>
        </div>
    </div>


    {{-- جدول عرض المصادر --}}
    <div class="card">
         <div class="card-header">
            <h5 class="card-title mb-0">قائمة المصادر التعليمية</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>العنوان</th>
                            <th>النوع</th>
                            <th>الرابط/المسار</th>
                            <th>العمر المستهدف</th>
                            <th>الموضوع</th>
                            <th>أضيف بواسطة</th>
                            <th>تاريخ الإضافة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($resources as $index => $resource)
                        <tr>
                            <td>{{ $resources->firstItem() + $index }}</td>
                            <td>
                                <a href="{{ route('admin.resources.show', $resource) }}">{{ Str::limit($resource->title, 40) }}</a>
                                @if($resource->description)
                                    <small class="d-block text-muted">{{ Str::limit($resource->description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                @php
                                    $typeBadge = match($resource->resource_type) { 'Video' => 'danger', 'Article' => 'info', 'Game' => 'success', 'Link' => 'primary', default => 'secondary' };
                                    $typeText = $resourceTypes[$resource->resource_type] ?? $resource->resource_type;
                                @endphp
                                <span class="badge bg-{{ $typeBadge }}">{{ $typeText }}</span>
                            </td>
                            <td>
                                {{-- عرض جزء من الرابط وإمكانية فتحه في نافذة جديدة --}}
                                <a href="{{ $resource->url_or_path }}" target="_blank" title="{{ $resource->url_or_path }}">
                                    {{ Str::limit($resource->url_or_path, 30) }} <i data-feather="external-link" style="width:12px; height:12px;"></i>
                                </a>
                            </td>
                             <td>
                                @if($resource->target_age_min && $resource->target_age_max)
                                    {{ $resource->target_age_min }} - {{ $resource->target_age_max }} سنوات
                                @elseif($resource->target_age_min)
                                    {{ $resource->target_age_min }}+ سنوات
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                             <td>{{ $resource->subject ?? '-' }}</td>
                             <td>{{ $resource->addedByAdmin->user->name ?? 'N/A' }}</td>
                            <td>{{ $resource->added_at ? $resource->added_at->format('Y-m-d') : $resource->created_at->format('Y-m-d') }}</td>
                            <td>
                                <a href="{{ route('admin.resources.show',  ['educationalResource' => $resource]) }}" class="btn btn-sm btn-info me-1" title="عرض">
                                    <i data-feather="eye"></i>
                                </a>
                                <a href="{{ route('admin.resources.edit',  ['educationalResource' => $resource]) }}" class="btn btn-sm btn-warning me-1" title="تعديل">
                                    <i data-feather="edit-2"></i>
                                </a>
                                <form action="{{ route('admin.resources.destroy',  ['educationalResource' => $resource]) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا المصدر التعليمي؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                        <i data-feather="trash-2"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">لا توجد مصادر تعليمية تطابق البحث أو الفلترة.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
         @if ($resources->hasPages())
            <div class="card-footer">
                {{ $resources->links() }}
            </div>
        @endif
    </div> {{-- نهاية الـ card --}}
</div>
@endsection