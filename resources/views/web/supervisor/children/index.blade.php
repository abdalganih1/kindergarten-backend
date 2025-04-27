@extends('layouts.supervisor') {{-- أو layouts.admin --}}

@section('title', 'عرض الأطفال')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">عرض الأطفال في الفصول التي تشرف عليها</h2>

     {{-- رسالة إذا لم يكن المشرف مسؤولاً عن أي فصول --}}
    @if(isset($noClassesAssigned) && $noClassesAssigned)
        <div class="alert alert-warning" role="alert">
            لم يتم تعيين أي فصول دراسية لك حتى الآن.
        </div>
    @else
        {{-- قسم الفلترة والبحث --}}
        <div class="card mb-4">
            <div class="card-header">
               <i data-feather="filter" class="me-1"></i> فلترة وعرض الأطفال
            </div>
            <div class="card-body">
                 <form method="GET" action="{{ route('supervisor.children.index') }}" class="row g-3 align-items-end">
                    {{-- حقل البحث --}}
                    <div class="col-md-6">
                        <label for="search" class="form-label">بحث بالاسم:</label>
                        <input type="text" class="form-control form-control-sm" id="search" name="search" placeholder="الاسم الأول أو الأخير..." value="{{ $searchTerm ?? '' }}">
                    </div>

                    {{-- فلتر الفصل (فصول المشرف فقط) --}}
                    <div class="col-md-4">
                        <label for="class_id" class="form-label">الفصل الدراسي:</label>
                        <select class="form-select form-select-sm" id="class_id" name="class_id">
                            <option value="">-- كل فصولي --</option>
                            @foreach($supervisedClasses as $id => $name)
                                <option value="{{ $id }}" {{ $selectedClassId == $id ? 'selected' : '' }}>{{ $name }}</option>
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
                                <th>العمر</th>
                                <th>الجنس</th>
                                <th>الفصل</th>
                                <th>أولياء الأمور</th>
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
                                    <a href="{{ route('supervisor.children.show', $child) }}">{{ $child->full_name }}</a>
                                </td>
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
                                <td>
                                    <a href="{{ route('supervisor.children.show', $child) }}" class="btn btn-sm btn-info me-1" title="عرض الملف">
                                        <i data-feather="eye"></i>
                                    </a>
                                    {{-- لا توجد أزرار تعديل أو حذف للمشرف هنا --}}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">لا يوجد أطفال لعرضهم بناءً على الفلترة الحالية أو في الفصول التي تشرف عليها.</td>
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
    @endif {{-- نهاية التحقق من وجود فصول للمشرف --}}
</div>
@endsection
