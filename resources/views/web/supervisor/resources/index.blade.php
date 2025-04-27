@extends('layouts.supervisor') {{-- أو layouts.admin --}}

@section('title', 'إدارة المصادر التعليمية')

@section('header-buttons')
    {{-- التحقق مباشرة من دور المستخدم لإظهار زر الإضافة --}}
    @if(Auth::check() && Auth::user()->role === 'Supervisor')
        <a href="{{ route('supervisor.resources.create') }}" class="btn btn-sm btn-success">
            <i data-feather="plus-circle" class="me-1"></i> إضافة مصدر جديد
        </a>
    @endif
@endsection

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">المصادر التعليمية</h2>

    {{-- قسم الفلترة والبحث (مطابق لقسم المدير) --}}
    <div class="card mb-4">
        <div class="card-header">
           <i data-feather="filter" class="me-1"></i> فلترة وبحث المصادر
        </div>
        <div class="card-body">
             <form method="GET" action="{{ route('supervisor.resources.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="search" class="form-label">بحث بالعنوان/الوصف:</label>
                    <input type="text" class="form-control form-control-sm" id="search" name="search" placeholder="كلمة مفتاحية..." value="{{ $searchTerm ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label for="resource_type" class="form-label">نوع المصدر:</label>
                    <select class="form-select form-select-sm" id="resource_type" name="resource_type">
                        <option value="">-- الكل --</option>
                         {{-- التأكد من وجود المتغير قبل التكرار --}}
                        @if(isset($resourceTypes) && is_array($resourceTypes))
                            @foreach($resourceTypes as $value => $label)
                                <option value="{{ $value }}" {{ isset($resourceType) && $resourceType == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                 <div class="col-md-3">
                    <label for="subject" class="form-label">الموضوع:</label>
                    <input type="text" class="form-control form-control-sm" id="subject" name="subject" placeholder="مثل: لغة عربية..." value="{{ $subject ?? '' }}">
                </div>
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
                        {{-- التأكد من وجود المتغير قبل التكرار --}}
                        @if(isset($resources))
                            @forelse ($resources as $index => $resource)
                            <tr>
                                <td>{{ $resources->firstItem() + $index }}</td>
                                <td>
                                    {{-- رابط العرض يشير لمسار المشرف --}}
                                    <a href="{{ route('supervisor.resources.show', $resource) }}">{{ Str::limit($resource->title, 40) }}</a>
                                    @if($resource->description)
                                        <small class="d-block text-muted">{{ Str::limit($resource->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        // وضع $resourceTypes داخل التحقق للتأكد من وجودها
                                        if(isset($resourceTypes) && is_array($resourceTypes)){
                                            $typeBadge = match($resource->resource_type) { 'Video' => 'danger', 'Article' => 'info', 'Game' => 'success', 'Link' => 'primary', default => 'secondary' };
                                            $typeText = $resourceTypes[$resource->resource_type] ?? $resource->resource_type;
                                        } else {
                                            $typeBadge = 'secondary';
                                            $typeText = $resource->resource_type;
                                        }
                                    @endphp
                                    <span class="badge bg-{{ $typeBadge }}">{{ $typeText }}</span>
                                </td>
                                <td>
                                    <a href="{{ $resource->url_or_path }}" target="_blank" title="{{ $resource->url_or_path }}">
                                        {{ Str::limit($resource->url_or_path, 30) }} <i data-feather="external-link" style="width:12px; height:12px;"></i>
                                    </a>
                                </td>
                                <td>
                                    @if($resource->target_age_min && $resource->target_age_max) {{ $resource->target_age_min }} - {{ $resource->target_age_max }} @elseif($resource->target_age_min) {{ $resource->target_age_min }}+ @elseif($resource->target_age_max) حتى {{ $resource->target_age_max }} @else <span class="text-muted">-</span> @endif
                                </td>
                                <td>{{ $resource->subject ?? '-' }}</td>
                                <td>{{ $resource->addedByAdmin->user->name ?? 'N/A' }}</td>
                                <td>{{ $resource->created_at ? $resource->created_at->format('Y-m-d') : 'N/A' }}</td> {{-- استخدام created_at والتحقق من وجودها --}}
                                <td>
                                    {{-- زر العرض متاح دائمًا للمشرف الذي يصل لهذه الصفحة --}}
                                    <a href="{{ route('supervisor.resources.show', $resource) }}" class="btn btn-sm btn-info me-1" title="عرض">
                                        <i data-feather="eye"></i>
                                    </a>

                                    {{-- أزرار التعديل والحذف بناءً على دور المشرف --}}
                                    @if(Auth::check() && Auth::user()->role === 'Supervisor')
                                        {{-- يمكنك إضافة تحقق إضافي هنا للملكية إذا لزم الأمر --}}
                                        {{-- @if($resource->added_by_id === Auth::user()->adminProfile?->admin_id) --}}
                                            <a href="{{ route('supervisor.resources.edit', $resource) }}" class="btn btn-sm btn-warning me-1" title="تعديل">
                                                <i data-feather="edit-2"></i>
                                            </a>
                                            <form action="{{ route('supervisor.resources.destroy', $resource) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا المصدر التعليمي؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                                    <i data-feather="trash-2"></i>
                                                </button>
                                            </form>
                                        {{-- @endif --}}
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">لا توجد مصادر تعليمية تطابق البحث أو الفلترة.</td>
                            </tr>
                            @endforelse
                        @else
                             <tr>
                                <td colspan="9" class="text-center py-4 text-danger">حدث خطأ: بيانات المصادر غير متاحة.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
         {{-- التأكد من وجود المتغير قبل استخدامه --}}
         @if (isset($resources) && $resources->hasPages())
            <div class="card-footer">
                {{ $resources->links() }}
            </div>
        @endif
    </div> {{-- نهاية الـ card --}}
</div>
@endsection