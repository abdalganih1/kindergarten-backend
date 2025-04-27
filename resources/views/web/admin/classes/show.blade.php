@extends('layouts.admin')

@section('title', 'تفاصيل الفصل: ' . $kindergartenClass->class_name)

@section('header-buttons')
    <a href="{{ route('admin.classes.edit', $kindergartenClass) }}" class="btn btn-sm btn-warning me-2">
        <i data-feather="edit-2" class="me-1"></i> تعديل الفصل
    </a>
    <form action="{{ route('admin.classes.destroy', $kindergartenClass) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا الفصل؟ لن تتمكن من الحذف إذا كان هناك أطفال مسجلون فيه.');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger me-2">
            <i data-feather="trash-2" class="me-1"></i> حذف الفصل
        </button>
    </form>
    <a href="{{ route('admin.classes.index') }}" class="btn btn-sm btn-outline-secondary">
        <i data-feather="arrow-left" class="me-1"></i> العودة إلى القائمة
    </a>
@endsection

@section('content')
<div class="container-fluid">
    {{-- تفاصيل الفصل --}}
     <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">تفاصيل الفصل الدراسي</h5>
        </div>
        <div class="card-body">
             <h3 class="card-title border-bottom pb-2 mb-3">{{ $kindergartenClass->class_name }}</h3>
             <dl class="row mb-0">
                <dt class="col-sm-3">الوصف:</dt>
                <dd class="col-sm-9">{{ $kindergartenClass->description ?: '-' }}</dd>

                 <dt class="col-sm-3">العمر المستهدف:</dt>
                 <dd class="col-sm-9">
                     @if($kindergartenClass->min_age !== null && $kindergartenClass->max_age !== null)
                        {{ $kindergartenClass->min_age }} - {{ $kindergartenClass->max_age }} سنوات
                    @elseif ($kindergartenClass->min_age !== null)
                        {{ $kindergartenClass->min_age }}+ سنوات
                    @elseif ($kindergartenClass->max_age !== null)
                        حتى {{ $kindergartenClass->max_age }} سنوات
                    @else
                        <span class="text-muted">غير محدد</span>
                    @endif
                 </dd>

                 <dt class="col-sm-3">تاريخ الإنشاء:</dt>
                 <dd class="col-sm-9">{{ $kindergartenClass->created_at->format('Y-m-d H:i A') }}</dd>

                 <dt class="col-sm-3">آخر تحديث:</dt>
                 <dd class="col-sm-9">{{ $kindergartenClass->updated_at->format('Y-m-d H:i A') }}</dd>
             </dl>
        </div>
    </div>

    {{-- قائمة الأطفال في الفصل --}}
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0"><i data-feather="users" class="me-1"></i> الأطفال المسجلون في الفصل ({{ $children->total() }})</h5>
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
                            <th>أولياء الأمور</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($children as $index => $child)
                        <tr>
                            <td>{{ $children->firstItem() + $index }}</td>
                             <td>
                                <img src="{{ $child->photo_url ? Storage::disk('public')->url($child->photo_url) : asset('images/default-avatar.png') }}" alt="{{ $child->full_name }}" class="rounded-circle" width="35" height="35" style="object-fit: cover;">
                            </td>
                            <td>
                                <a href="{{ route('admin.children.show', $child) }}">{{ $child->full_name }}</a>
                            </td>
                            <td>{{ $child->date_of_birth ? \Carbon\Carbon::parse($child->date_of_birth)->format('Y-m-d') : 'N/A' }}</td>
                            <td>{{ $child->date_of_birth ? \Carbon\Carbon::parse($child->date_of_birth)->age : 'N/A' }}</td>
                            <td>{{ $child->gender ?? 'N/A' }}</td>
                            <td>
                                @forelse($child->parents as $parent)
                                    <span class="badge bg-light text-dark border me-1">{{ $parent->full_name }}</span>
                                @empty
                                    <span class="text-muted">-</span>
                                @endforelse
                            </td>
                            <td>
                                <a href="{{ route('admin.children.show', $child) }}" class="btn btn-sm btn-outline-info me-1" title="عرض ملف الطفل">
                                    <i data-feather="eye"></i>
                                </a>
                                <a href="{{ route('admin.children.edit', $child) }}" class="btn btn-sm btn-outline-warning" title="تعديل ملف الطفل">
                                    <i data-feather="edit-2"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">لا يوجد أطفال مسجلون في هذا الفصل حاليًا.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
         @if ($children->hasPages())
            <div class="card-footer">
                {{-- استخدام اسم الصفحة المخصص للـ pagination --}}
                {{ $children->links() }}
            </div>
        @endif
    </div> {{-- نهاية card الأطفال --}}

</div>
@endsection