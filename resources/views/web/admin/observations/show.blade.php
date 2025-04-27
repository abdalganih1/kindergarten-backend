@extends('layouts.admin')

@section('title', 'تفاصيل الملاحظة')

@section('header-buttons')
     <form action="{{ route('admin.observations.destroy', $observation) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه الملاحظة؟');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger me-2">
            <i data-feather="trash-2" class="me-1"></i> حذف الملاحظة
        </button>
    </form>
    <a href="{{ route('admin.observations.index') }}" class="btn btn-sm btn-outline-secondary">
        <i data-feather="arrow-left" class="me-1"></i> العودة إلى القائمة
    </a>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">تفاصيل الملاحظة</h5>
        </div>
        <div class="card-body">
             <dl class="row mb-3 pb-3 border-bottom">
                 <dt class="col-sm-3">مقدم الملاحظة:</dt>
                 <dd class="col-sm-9">{{ $observation->parentSubmitter->user->name ?? 'N/A' }} <small class="text-muted">({{ $observation->parentSubmitter->user->email ?? '' }})</small></dd>

                <dt class="col-sm-3">تاريخ الإرسال:</dt>
                <dd class="col-sm-9">{{ $observation->submitted_at->format('Y-m-d H:i A') }} ({{ $observation->submitted_at->diffForHumans() }})</dd>

                <dt class="col-sm-3">مرتبطة بالطفل:</dt>
                <dd class="col-sm-9">
                    @if($observation->child)
                        <a href="{{ route('admin.children.show', $observation->child) }}">{{ $observation->child->full_name }}</a>
                         @if($observation->child->kindergartenClass)
                            <span class="text-muted"> ({{ $observation->child->kindergartenClass->class_name }})</span>
                         @endif
                    @else
                        <span class="text-muted">(ملاحظة عامة غير مرتبطة بطفل معين)</span>
                    @endif
                </dd>
             </dl>

             <h6 class="mt-4">نص الملاحظة:</h6>
            <div class="message-body p-3 bg-light border rounded" style="white-space: pre-wrap;">
                {{ $observation->observation_text }}
            </div>

        </div>
    </div>
</div>
@endsection