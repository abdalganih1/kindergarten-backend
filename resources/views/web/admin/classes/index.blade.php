@extends('layouts.admin')

@section('title', 'إدارة الفصول الدراسية')

@section('header-buttons')
<a href="{{ route('admin.classes.create') }}" class="btn btn-sm btn-success">
    <i data-feather="plus-circle" class="me-1"></i> إضافة فصل جديد
</a>
@endsection

@section('content')
<div class="container-fluid">

    {{-- لا حاجة لفلترة معقدة هنا عادةً، يمكن إضافتها إذا لزم الأمر --}}

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">قائمة الفصول الدراسية</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>اسم الفصل</th>
                            <th>الوصف</th>
                            <th>العمر (Min-Max)</th>
                            <th>عدد الأطفال</th>
                            <th>تاريخ الإنشاء</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($classes as $index => $class)
                        <tr>
                            <td>{{ $classes->firstItem() + $index }}</td>
                            <td>
                                <a href="{{ route('admin.classes.show', $class) }}">
                                    {{ $class->class_name }}
                                </a>
                            </td>
                            <td>{{ Str::limit($class->description, 70) ?: '-' }}</td>
                             <td>
                                @if($class->min_age !== null && $class->max_age !== null)
                                    {{ $class->min_age }} - {{ $class->max_age }}
                                @elseif ($class->min_age !== null)
                                    {{ $class->min_age }}+
                                @elseif ($class->max_age !== null)
                                    حتى {{ $class->max_age }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                             </td>
                            <td>
                                <span class="badge bg-primary rounded-pill">{{ $class->children_count }}</span>
                            </td>
                            <td>{{ $class->created_at->format('Y-m-d') }}</td>
                            <td>
                                <a href="{{ route('admin.classes.show', $class) }}" class="btn btn-sm btn-info me-1" title="عرض التفاصيل والأطفال">
                                    <i data-feather="eye"></i>
                                </a>
                                <a href="{{ route('admin.classes.edit', $class) }}" class="btn btn-sm btn-warning me-1" title="تعديل">
                                    <i data-feather="edit-2"></i>
                                </a>
                                <form action="{{ route('admin.classes.destroy', $class) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا الفصل؟ لن تتمكن من الحذف إذا كان هناك أطفال مسجلون فيه.');">
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
                            <td colspan="7" class="text-center py-4">لا توجد فصول دراسية لعرضها. قم بإضافة فصل جديد.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
         @if ($classes->hasPages())
            <div class="card-footer">
                {{ $classes->links() }}
            </div>
        @endif
    </div> {{-- نهاية الـ card --}}
</div>
@endsection