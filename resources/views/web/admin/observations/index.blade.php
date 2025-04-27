@extends('layouts.admin')

@section('title', 'ملاحظات أولياء الأمور')

@section('content')
<div class="container-fluid">

    {{-- قسم الفلترة والبحث --}}
    <div class="card mb-4">
        <div class="card-header">
           <i data-feather="filter" class="me-1"></i> فلترة وبحث الملاحظات
        </div>
        <div class="card-body">
             <form method="GET" action="{{ route('admin.observations.index') }}" class="row g-3 align-items-end">
                {{-- فلتر ولي الأمر --}}
                <div class="col-md-3">
                    <label for="parent_id" class="form-label">ولي الأمر:</label>
                    <select class="form-select form-select-sm" id="parent_id" name="parent_id">
                        <option value="">-- الكل --</option>
                        @foreach($parents as $id => $name)
                            <option value="{{ $id }}" {{ $parentId == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- فلتر الطفل --}}
                 <div class="col-md-3">
                    <label for="child_id" class="form-label">الطفل:</label>
                    <select class="form-select form-select-sm" id="child_id" name="child_id">
                        <option value="">-- الكل / ملاحظات عامة --</option>
                        @foreach($children as $id => $name)
                            <option value="{{ $id }}" {{ $childId == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- حقل البحث --}}
                <div class="col-md-4">
                    <label for="search" class="form-label">بحث في نص الملاحظة:</label>
                    <input type="text" class="form-control form-control-sm" id="search" name="search" placeholder="كلمة مفتاحية..." value="{{ $searchTerm ?? '' }}">
                </div>


                {{-- زر تطبيق الفلتر --}}
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">بحث</button>
                </div>
            </form>
        </div>
    </div>


    {{-- جدول عرض الملاحظات --}}
    <div class="card">
         <div class="card-header">
            <h5 class="card-title mb-0">قائمة الملاحظات الواردة</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>مقدم الملاحظة</th>
                            <th>عن الطفل</th>
                            <th>نص الملاحظة</th>
                            <th>تاريخ الإرسال</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($observations as $index => $observation)
                        <tr>
                            <td>{{ $observations->firstItem() + $index }}</td>
                            <td>{{ $observation->parentSubmitter->user->name ?? 'N/A' }}</td>
                            <td>
                                @if($observation->child)
                                    <a href="{{ route('admin.children.show', $observation->child) }}">{{ $observation->child->full_name }}</a>
                                @else
                                    <span class="text-muted">(ملاحظة عامة)</span>
                                @endif
                            </td>
                            <td>{{ Str::limit($observation->observation_text, 80) }}</td>
                            <td>{{ $observation->submitted_at->format('Y-m-d H:i') }} <small class="text-muted">({{ $observation->submitted_at->diffForHumans() }})</small></td>
                            <td>
                                <a href="{{ route('admin.observations.show', $observation) }}" class="btn btn-sm btn-info me-1" title="عرض التفاصيل">
                                    <i data-feather="eye"></i>
                                </a>
                                <form action="{{ route('admin.observations.destroy', $observation) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه الملاحظة؟');">
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
                            <td colspan="6" class="text-center py-4">لا توجد ملاحظات تطابق الفلترة الحالية أو لم يتم استلام أي ملاحظات بعد.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
         @if ($observations->hasPages())
            <div class="card-footer">
                {{ $observations->links() }}
            </div>
        @endif
    </div> {{-- نهاية الـ card --}}
</div>
@endsection