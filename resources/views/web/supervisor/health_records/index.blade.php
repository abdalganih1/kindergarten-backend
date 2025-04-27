@extends('layouts.supervisor') {{-- أو layouts.admin --}}

@section('title', 'السجلات الصحية للأطفال')

@section('header-buttons')
    {{-- زر الإضافة يظهر فقط إذا كان المشرف معينًا لفصول ولديه أطفال --}}
    @if(!isset($noClassesAssigned) || !$noClassesAssigned)
        @if(isset($children) && !$children->isEmpty())
            <a href="{{ route('supervisor.health-records.create') }}" class="btn btn-sm btn-success">
                <i data-feather="plus-circle" class="me-1"></i> إضافة سجل صحي جديد
            </a>
        @endif
    @endif
@endsection

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">السجلات الصحية (فصولي)</h2>

    @if(isset($noClassesAssigned) && $noClassesAssigned)
        <div class="alert alert-warning" role="alert">
           <i data-feather="alert-circle" class="me-1 align-text-bottom"></i>
           لم يتم تعيين أي فصول دراسية لك حتى الآن. لا يمكنك إدارة السجلات الصحية.
        </div>
    @else
        {{-- قسم الفلترة --}}
        <div class="card mb-4">
            <div class="card-header">
               <i data-feather="filter" class="me-1"></i> فلترة السجلات الصحية
            </div>
            <div class="card-body">
                 <form method="GET" action="{{ route('supervisor.health-records.index') }}" class="row g-3 align-items-end">
                    {{-- فلتر الطفل (فقط أطفال المشرف) --}}
                    <div class="col-md-5">
                        <label for="child_id" class="form-label">الطفل:</label>
                        <select class="form-select form-select-sm" id="child_id" name="child_id">
                            <option value="">-- كل الأطفال في فصولي --</option>
                            @foreach($children as $id => $name)
                                <option value="{{ $id }}" {{ $selectedChildId == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                         @if($children->isEmpty()) <small class="text-danger">لا يوجد أطفال في فصولك.</small> @endif
                    </div>

                    {{-- فلتر نوع السجل --}}
                    <div class="col-md-5">
                        <label for="record_type" class="form-label">نوع السجل:</label>
                        <select class="form-select form-select-sm" id="record_type" name="record_type">
                            <option value="">-- كل الأنواع --</option>
                             @foreach($recordTypes as $value => $label)
                                <option value="{{ $value }}" {{ $selectedType == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- زر تطبيق الفلتر --}}
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">عرض</button>
                    </div>
                </form>
            </div>
        </div>


        {{-- جدول عرض السجلات الصحية --}}
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">قائمة السجلات الصحية</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>اسم الطفل</th>
                                <th>الفصل</th>
                                <th>تاريخ السجل</th>
                                <th>النوع</th>
                                <th>التفاصيل</th>
                                <th>الاستحقاق القادم</th>
                                <th>المستند</th>
                                <th>أُدخل بواسطة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($healthRecords as $index => $record)
                            <tr>
                                <td>{{ $healthRecords->firstItem() + $index }}</td>
                                <td><a href="{{ route('supervisor.children.show', $record->child_id) }}">{{ $record->child->full_name ?? 'N/A' }}</a></td>
                                <td>{{ $record->child->kindergartenClass->class_name ?? 'N/A' }}</td>
                                <td>{{ $record->record_date ? \Carbon\Carbon::parse($record->record_date)->format('Y-m-d') : 'N/A' }}</td>
                                <td>{{ $recordTypes[$record->record_type] ?? $record->record_type }}</td>
                                <td>{{ Str::limit($record->details, 50) }}</td>
                                <td>{{ $record->next_due_date ? \Carbon\Carbon::parse($record->next_due_date)->format('Y-m-d') : '-' }}</td>
                                <td>
                                    @if($record->document_path && Storage::disk('public')->exists($record->document_path))
                                        <a href="{{ Storage::disk('public')->url($record->document_path) }}" target="_blank" title="عرض المستند"><i data-feather="file-text"></i></a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $record->enteredByUser->name ?? 'N/A' }}</td>
                                <td>
                                    {{-- <a href="{{ route('supervisor.health-records.show', $record) }}" class="btn btn-sm btn-info me-1" title="عرض">
                                        <i data-feather="eye"></i>
                                    </a> --}}
                                    <a href="{{ route('supervisor.health-records.edit', $record) }}" class="btn btn-sm btn-warning me-1" title="تعديل">
                                        <i data-feather="edit-2"></i>
                                    </a>
                                    <form action="{{ route('supervisor.health-records.destroy', $record) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا السجل الصحي؟');">
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
                                <td colspan="10" class="text-center py-4">لا توجد سجلات صحية تطابق الفلترة الحالية أو لم يتم إضافة سجلات بعد.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
             @if ($healthRecords->hasPages())
                <div class="card-footer">
                    {{ $healthRecords->links() }}
                </div>
            @endif
        </div> {{-- نهاية الـ card --}}
    @endif {{-- نهاية التحقق من وجود فصول للمشرف --}}
</div>
@endsection