@extends('layouts.admin') {{-- استخدام الـ layout الأساسي --}}

@section('title', 'إدارة الإعلانات') {{-- عنوان الصفحة --}}

{{-- زر إضافة جديد في الهيدر --}}
@section('header-buttons')
<a href="{{ route('admin.announcements.create') }}" class="btn btn-sm btn-success">
    <i data-feather="plus-circle" class="me-1"></i> إضافة إعلان جديد
</a>
@endsection

@section('content')
<div class="container-fluid">
    {{-- <h2 class="mb-4">قائمة الإعلانات</h2> --}} {{-- العنوان موجود في الهيدر الآن --}}

    {{-- يمكنك إضافة قسم للفلترة/البحث هنا لاحقًا إذا لزم الأمر --}}
    {{-- <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.announcements.index') }}">
                // عناصر الفلترة (مثال: حسب الفصل، حسب التاريخ، بحث عن كلمة)
            </form>
        </div>
    </div> --}}

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">قائمة الإعلانات</h5>
        </div>
        <div class="card-body p-0"> {{-- إزالة الحشوة الافتراضية للجسم للسماح للجدول بأخذ العرض الكامل --}}
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>العنوان</th>
                            <th>المؤلف</th>
                            <th>الفصل المستهدف</th>
                            <th>تاريخ النشر</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($announcements as $index => $announcement)
                        <tr>
                            <td>{{ $announcements->firstItem() + $index }}</td>
                            <td>
                                <a href="{{ route('admin.announcements.show', $announcement) }}">
                                    {{ Str::limit($announcement->title, 50) }} {{-- تحديد طول العنوان --}}
                                </a>
                            </td>
                            <td>{{ $announcement->author->user->name ?? 'N/A' }}</td> {{-- عرض اسم المستخدم للمؤلف --}}
                            <td>
                                @if($announcement->targetClass)
                                    <span class="badge bg-info">{{ $announcement->targetClass->class_name }}</span>
                                @else
                                    <span class="badge bg-secondary">كل الفصول</span>
                                @endif
                            </td>
                            <td>{{ $announcement->publish_date ? $announcement->publish_date->format('Y-m-d H:i') : $announcement->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.announcements.show', $announcement) }}" class="btn btn-sm btn-info me-1" title="عرض">
                                    <i data-feather="eye"></i>
                                </a>
                                <a href="{{ route('admin.announcements.edit', $announcement) }}" class="btn btn-sm btn-warning me-1" title="تعديل">
                                    <i data-feather="edit-2"></i>
                                </a>
                                {{-- زر الحذف مع تأكيد --}}
                                <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا الإعلان؟');">
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
                            <td colspan="6" class="text-center py-4">لا توجد إعلانات لعرضها حاليًا.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($announcements->hasPages())
            <div class="card-footer">
                {{-- روابط الـ Pagination --}}
                {{ $announcements->links() }}
            </div>
        @endif
    </div> {{-- نهاية الـ card --}}

</div>
@endsection