@extends('layouts.supervisor') {{-- أو layouts.admin --}}

@section('title', 'عرض الرسالة')

@section('header-buttons')
     <form action="{{ route('supervisor.messages.destroy', $message) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه الرسالة؟');"> {{-- مسار المشرف --}}
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger me-2">
            <i data-feather="trash-2" class="me-1"></i> حذف الرسالة
        </button>
    </form>
    <a href="{{ route('supervisor.messages.index') }}" class="btn btn-sm btn-outline-secondary"> {{-- مسار المشرف --}}
        <i data-feather="arrow-left" class="me-1"></i> العودة إلى الصندوق
    </a>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                الموضوع: {{ $message->subject ?: '(بدون موضوع)' }}
            </h5>
            <span class="text-muted small">
                 {{ $message->sent_at->format('Y-m-d H:i A') }} ({{ $message->sent_at->diffForHumans() }})
            </span>
        </div>
        <div class="card-body">
            {{-- نفس محتوى عرض رسالة المدير --}}
             <div class="row mb-3 pb-3 border-bottom">
                <div class="col-md-6">
                    <strong>من:</strong>
                    {{ $message->sender->name ?? 'غير معروف' }}
                    <span class="text-muted">({{ $message->sender->role ?? 'N/A' }})</span>
                     <br>
                     <small class="text-muted">{{ $message->sender->email ?? '' }}</small>
                </div>
                <div class="col-md-6 text-md-end">
                     <strong>إلى:</strong>
                     {{ $message->recipient->name ?? 'غير معروف' }}
                     <span class="text-muted">({{ $message->recipient->role ?? 'N/A' }})</span>
                     <br>
                     <small class="text-muted">{{ $message->recipient->email ?? '' }}</small>
                </div>
            </div>
            <h6 class="mt-4">نص الرسالة:</h6>
            <div class="message-body p-3 bg-light border rounded" style="white-space: pre-wrap;">{{ $message->body }}</div>
             <div class="mt-3 text-muted small">
                 @if($message->recipient_id === Auth::id())
                    @if($message->read_at)<i data-feather="check-circle" class="text-success me-1"></i> تم القراءة في: {{ $message->read_at->format('Y-m-d H:i') }}@else<i data-feather="circle" class="text-warning me-1"></i> لم تقرأ بعد@endif
                 @else<i data-feather="send" class="me-1"></i> رسالة مرسلة@endif
             </div>
        </div>
         <div class="card-footer text-end">
            {{-- زر الرد يوجه لنموذج إنشاء رسالة مع ملء المستقبل والموضوع --}}
             @if($message->sender_id !== Auth::id())
                <a href="{{ route('supervisor.messages.create', ['reply_to' => $message->sender_id, 'subject' => 'RE: ' . $message->subject]) }}" class="btn btn-primary"><i data-feather="corner-up-left" class="me-1"></i> رد على الرسالة</a>
             @endif
         </div>
    </div>
</div>
@endsection