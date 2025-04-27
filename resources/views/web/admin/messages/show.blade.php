@extends('layouts.admin')

@section('title', 'عرض الرسالة')

@section('header-buttons')
     {{-- زر الحذف --}}
     <form action="{{ route('admin.messages.destroy', $message) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه الرسالة نهائيًا؟');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger me-2">
            <i data-feather="trash-2" class="me-1"></i> حذف الرسالة
        </button>
    </form>
    {{-- زر العودة --}}
    <a href="{{ route('admin.messages.index') }}" class="btn btn-sm btn-outline-secondary">
        <i data-feather="arrow-left" class="me-1"></i> العودة إلى الصندوق
    </a>
@endsection

@section('content')
<div class="container-fluid">
    {{-- عرض تفاصيل الرسالة الأصلية --}}
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                الموضوع: {{ $message->subject ?: '(بدون موضوع)' }}
            </h5>
            <span class="text-muted small">
                 {{ $message->sent_at->format('Y-m-d H:i A') }} ({{ $message->sent_at->diffForHumans() }})
            </span>
        </div>
        <div class="card-body">
            {{-- تفاصيل المرسل والمستقبل --}}
            <div class="row mb-3 pb-3 border-bottom">
                <div class="col-md-6">
                    <strong>من:</strong>
                    {{ $message->sender->name ?? 'غير معروف' }}
                    <span class="text-muted">({{ $message->sender->role ?? 'N/A' }})</span>
                    @if($message->sender->parentProfile)<span class="small text-info"> (ولي أمر)</span>@elseif($message->sender->adminProfile)<span class="small text-danger"> (مدير)</span>@endif
                    <br>
                    <small class="text-muted">{{ $message->sender->email ?? '' }}</small>
                </div>
                <div class="col-md-6 text-md-end">
                     <strong>إلى:</strong>
                     {{ $message->recipient->name ?? 'غير معروف' }}
                     <span class="text-muted">({{ $message->recipient->role ?? 'N/A' }})</span>
                     @if($message->recipient->parentProfile)<span class="small text-info"> (ولي أمر)</span>@elseif($message->recipient->adminProfile)<span class="small text-danger"> (مدير)</span>@endif
                     <br>
                     <small class="text-muted">{{ $message->recipient->email ?? '' }}</small>
                </div>
            </div>

            {{-- نص الرسالة الأصلي --}}
            <h6 class="mt-4">نص الرسالة:</h6>
            <div class="message-body p-3 bg-light border rounded" style="white-space: pre-wrap;">
                {{ $message->body }}
            </div>

            {{-- حالة القراءة --}}
             <div class="mt-3 text-muted small">
                 @if($message->recipient_id === Auth::id())
                    @if($message->read_at)
                        <i data-feather="check-circle" class="text-success me-1"></i> تم القراءة في: {{ $message->read_at->format('Y-m-d H:i') }}
                    @else
                         <i data-feather="circle" class="text-warning me-1"></i> لم تقرأ بعد
                    @endif
                 @else
                     <i data-feather="send" class="me-1"></i> رسالة مرسلة
                 @endif
             </div>

        </div>
    </div>

    {{-- نموذج الرد (يظهر فقط إذا لم تكن الرسالة مرسلة من المدير الحالي) --}}
    @if($message->sender_id !== Auth::id())
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0"><i data-feather="corner-up-left" class="me-1"></i> الرد على الرسالة</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.messages.store') }}" method="POST">
                @csrf
                {{-- حقل مخفي للمستلم (مرسل الرسالة الأصلية) --}}
                {{-- التأكد من وجود $replyRecipient قبل استخدامه --}}
                <input type="hidden" name="recipient_id" value="{{ $replyRecipient->id ?? '' }}">

                {{-- حقل الموضوع (مع RE:) --}}
                 <div class="mb-3">
                    <label for="subject" class="form-label">الموضوع</label>
                    <input type="text" class="form-control @error('subject') is-invalid @enderror" id="subject" name="subject" value="{{ old('subject', $replySubject ?? '') }}">
                    {{-- === تعديل: إزالة $message === --}}
                    @error('subject') <div class="invalid-feedback">{{-- - --}}</div> @enderror
                </div>

                {{-- حقل نص الرد --}}
                 <div class="mb-3">
                    <label for="body" class="form-label">نص الرد <span class="text-danger">*</span></label>
                     {{-- اقتباس الرسالة الأصلية (اختياري) --}}
                     {{-- يجب التأكد من وجود $message هنا لأنه خارج نطاق @error --}}
                    <textarea class="form-control @error('body') is-invalid @enderror" id="body" name="body" rows="5" required>{{ old('body', "\n\n\n----------------\n> " . str_replace("\n", "\n> ", $message->body ?? '')) }}</textarea>
                    {{-- === تعديل: إزالة $message === --}}
                    @error('body') <div class="invalid-feedback">{{-- - --}}</div> @enderror
                </div>

                <div class="d-flex justify-content-end mt-3">
                    {{-- التأكد من وجود $replyRecipient قبل تفعيل الزر --}}
                    <button type="submit" class="btn btn-primary" {{ isset($replyRecipient) ? '' : 'disabled' }}>
                        <i data-feather="send" class="me-1"></i> إرسال الرد
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
@endsection