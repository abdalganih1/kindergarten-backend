@extends('layouts.admin')

@section('title', 'إدارة الأطفال')

@section('header-buttons')
<a href="{{ route('admin.children.create') }}" class="btn btn-sm btn-success">
    <i data-feather="user-plus" class="me-1"></i> إضافة طفل جديد
</a>
@endsection

@section('content')
<div class="container-fluid">

    {{-- قسم الفلترة والبحث --}}
    <div class="card mb-4">
        <div class="card-header">
           <i data-feather="filter" class="me-1"></i> فلترة وبحث
        </div>
        <div class="card-body">
             <form method="GET" action="{{ route('admin.children.index') }}" class="row g-3 align-items-end">
                {{-- حقل البحث --}}
                <div class="col-md-4">
                    <label for="search" class="form-label">بحث بالاسم:</label>
                    <input type="text" class="form-control form-control-sm" id="search" name="search" placeholder="الاسم الأول أو الأخير..." value="{{ $searchTerm ?? '' }}">
                </div>

                {{-- فلتر الفصل --}}
                <div class="col-md-3">
                    <label for="class_id" class="form-label">الفصل الدراسي:</label>
                    <select class="form-select form-select-sm" id="class_id" name="class_id">
                        <option value="">-- الكل --</option>
                        @foreach($classes as $id => $name)
                            <option value="{{ $id }}" {{ $selectedClassId == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- فلتر ولي الأمر --}}
                 <div class="col-md-3">
                    <label for="parent_id" class="form-label">ولي الأمر:</label>
                    <select class="form-select form-select-sm" id="parent_id" name="parent_id">
                        <option value="">-- الكل --</option>
                        @foreach($parents as $id => $name)
                            <option value="{{ $id }}" {{ $selectedParentId == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- زر تطبيق الفلتر --}}
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">بحث</button>
                </div>
            </form>
        </div>
    </div>


    {{-- جدول عرض الأطفال --}}
    <div class="card">
         <div class="card-header">
            <h5 class="card-title mb-0">قائمة الأطفال</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>الصورة</th>
                            <th>الاسم الكامل</th>
                            <th>تاريخ الميلاد</th>
                            <th>العمر</th>
                            <th>الجنس</th>
                            <th>الفصل</th>
                            <th>أولياء الأمور</th>
                            <th>تاريخ التسجيل</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($children as $index => $child)
                        <tr>
                            <td>{{ $children->firstItem() + $index }}</td>
                            <td>
                                <img src="{{ $child->photo_url ? Storage::disk('public')->url($child->photo_url) : asset('images/default-avatar.png') }}" alt="{{ $child->full_name }}" class="rounded-circle" width="40" height="40" style="object-fit: cover;">
                            </td>
                            <td>
                                <a href="{{ route('admin.children.show', $child) }}">{{ $child->full_name }}</a>
                            </td>
                            <td>{{ $child->date_of_birth ? \Carbon\Carbon::parse($child->date_of_birth)->format('Y-m-d') : 'N/A' }}</td>
                            <td>{{ $child->date_of_birth ? \Carbon\Carbon::parse($child->date_of_birth)->age : 'N/A' }} سنوات</td>
                            <td>{{ $child->gender ?? 'N/A' }}</td>
                            <td>
                                @if($child->kindergartenClass)
                                    <span class="badge bg-info">{{ $child->kindergartenClass->class_name }}</span>
                                @else
                                    <span class="badge bg-secondary">غير محدد</span>
                                @endif
                            </td>
                             <td>
                                @forelse($child->parents as $parent)
                                    <span class="badge bg-light text-dark border me-1">{{ $parent->full_name }}</span>
                                @empty
                                    <span class="text-muted">-</span>
                                @endforelse
                            </td>
                             <td>{{ $child->enrollment_date ? \Carbon\Carbon::parse($child->enrollment_date)->format('Y-m-d') : 'N/A' }}</td>
                            <td>
                                <a href="{{ route('admin.children.show', $child) }}" class="btn btn-sm btn-info me-1" title="عرض الملف">
                                    <i data-feather="eye"></i>
                                </a>
                                <a href="{{ route('admin.children.edit', $child) }}" class="btn btn-sm btn-warning me-1" title="تعديل">
                                    <i data-feather="edit-2"></i>
                                </a>
                                <form action="{{ route('admin.children.destroy', $child) }}" method="POST" class="d-inline" onsubmit="return confirm('تحذير! سيتم حذف ملف الطفل وجميع السجلات المرتبطة به (مثل الحضور، السجلات الصحية، التسجيل في الفعاليات). هل أنت متأكد؟');">
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
                            <td colspan="10" class="text-center py-4">لا يوجد أطفال لعرضهم بناءً على الفلترة الحالية.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
         @if ($children->hasPages())
            <div class="card-footer">
                {{ $children->links() }}
            </div>
        @endif
    </div> {{-- نهاية الـ card --}}
</div>
@endsection