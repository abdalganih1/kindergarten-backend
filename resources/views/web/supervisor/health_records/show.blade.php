@extends('layouts.supervisor') {{-- أو layouts.admin --}}

@section('title', 'تفاصيل السجل الصحي')

@section('header-buttons')
     <a href="{{ route('supervisor.health-records.edit', $healthRecord) }}" class="btn btn-sm btn-warning me-2">
        <i data-feather="edit-2" class="me-1"></i> تعديل السجل
    </a>
     <form action="{{ route('supervisor.health-records.destroy', $healthRecord) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا السجل الصحي؟');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger me-2">
            <i data-feather="trash-2" class="me-1"></i> حذف السجل
        </button>
    </form>
    <a href="{{ route('supervisor.health-records.index', ['child_id' => $healthRecord->child_id]) }}" class="btn btn-sm btn-outline-secondary">
        <i data-feather="arrow-left" class="me-1"></i> العودة إلى السجلات
    </a>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">تفاصيل السجل الصحي للطفل: {{ $healthRecord->child->full_name ?? 'N/A' }}</h5>
        </div>
        <div class="card-body">
             <dl class="row mb-0">
                <dt class="col-sm-3">الطفل:</dt>
                <dd class="col-sm-9"><a href="{{ route('supervisor.children.show', $healthRecord->child_id) }}">{{ $healthRecord->child->full_name ?? 'N/A' }}</a></dd>

                <dt class="col-sm-3">الفصل:</dt>
                <dd class="col-sm-9">{{ $healthRecord->child->kindergartenClass->class_name ?? 'N/A' }}</dd>

                <dt class="col-sm-3">تاريخ السجل:</dt>
                <dd class="col-sm-9">{{ $healthRecord->record_date ? \Carbon\Carbon::parse($healthRecord->record_date)->format('Y-m-d') : 'N/A' }}</dd>

                <dt class="col-sm-3">نوع السجل:</dt>
                <dd class="col-sm-9">{{ ['Vaccination'=>'تطعيم', 'Checkup'=>'فحص طبي', 'Illness'=>'مرض/إصابة', 'MedicationAdministered'=>'دواء تم إعطاؤه'][$healthRecord->record_type] ?? $healthRecord->record_type }}</dd>

                 <dt class="col-sm-3">التفاصيل:</dt>
                <dd class="col-sm-9" style="white-space: pre-wrap;">{{ $healthRecord->details }}</dd>

                <dt class="col-sm-3">تاريخ الاستحقاق القادم:</dt>
                <dd class="col-sm-9">{{ $healthRecord->next_due_date ? \Carbon\Carbon::parse($healthRecord->next_due_date)->format('Y-m-d') : '-' }}</dd>

                <dt class="col-sm-3">المستند المرفق:</dt>
                <dd class="col-sm-9">
                    @if($healthRecord->document_path && Storage::disk('public')->exists($healthRecord->document_path))
                        <a href="{{ Storage::disk('public')->url($healthRecord->document_path) }}" target="_blank">
                            <i data-feather="file-text" class="me-1"></i> عرض/تحميل المستند
                        </a>
                    @else
                        -
                    @endif
                </dd>

                 <dt class="col-sm-3">أُدخل بواسطة:</dt>
                 <dd class="col-sm-9">{{ $healthRecord->enteredByUser->name ?? 'N/A' }}</dd>

                 <dt class="col-sm-3">وقت الإدخال:</dt>
                 <dd class="col-sm-9">{{ $healthRecord->created_at->format('Y-m-d H:i A') }}</dd>

                 <dt class="col-sm-3">آخر تحديث:</dt>
                 <dd class="col-sm-9">{{ $healthRecord->updated_at->format('Y-m-d H:i A') }}</dd>
             </dl>
        </div>
    </div>
</div>
@endsection