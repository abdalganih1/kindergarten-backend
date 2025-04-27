@extends('layouts.supervisor') {{-- أو layouts.admin --}}

@section('title', 'إنشاء رسالة جديدة')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">إنشاء رسالة جديدة</h1>
         <a href="{{ route('supervisor.messages.index') }}" class="btn btn-outline-secondary">
            <i data-feather="arrow-right" class="me-1"></i> العودة إلى الصندوق
        </a>
    </div>

     <div class="card">
        <div class="card-body">
            <form action="{{ route('supervisor.messages.store') }}" method="POST">
                @csrf

                {{-- حقل اختيار المستقبل --}}
                <div class="mb-3">
                    <label for="recipient_id" class="form-label">إلى <span class="text-danger">*</span></label>
                    <select class="form-select @error('recipient_id') is-invalid @enderror" id="recipient_id" name="recipient_id" required>
                        <option value="" disabled {{ request('reply_to') ? '' : 'selected' }}>-- اختر المستلم --</option>
                        @if($recipients->where('role', 'Admin')->isNotEmpty())
                            <optgroup label="الإدارة">
                                @foreach($recipients->where('role', 'Admin') as $user)
                                    <option value="{{ $user->id }}" {{ old('recipient_id', request('reply_to')) == $user->id ? 'selected' : '' }}>{{ $user->name }} (مدير)</option>
                                @endforeach
                            </optgroup>
                        @endif
                         @if($recipients->where('role', 'Parent')->isNotEmpty())
                            <optgroup label="أولياء الأمور (فصولي)">
                                @foreach($recipients->where('role', 'Parent') as $user)
                                    <option value="{{ $user->id }}" {{ old('recipient_id', request('reply_to')) == $user->id ? 'selected' : '' }}>{{ $user->name }} (ولي أمر)</option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                    @error('recipient_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @if($recipients->isEmpty())
                        <small class="text-danger">لا يوجد مستخدمون يمكنك مراسلتهم حاليًا.</small>
                    @endif
                </div>

                 {{-- حقل الموضوع --}}
                <div class="mb-3">
                    <label for="subject" class="form-label">الموضوع</label>
                    <input type="text" class="form-control @error('subject') is-invalid @enderror" id="subject" name="subject" value="{{ old('subject', request('subject')) }}">
                    @error('subject') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                 {{-- حقل نص الرسالة --}}
                 <div class="mb-3">
                    <label for="body" class="form-label">نص الرسالة <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('body') is-invalid @enderror" id="body" name="body" rows="7" required>{{ old('body') }}</textarea>
                    @error('body') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>


                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('supervisor.messages.index') }}" class="btn btn-outline-secondary me-2">إلغاء</a>
                    <button type="submit" class="btn btn-primary" {{ $recipients->isEmpty() ? 'disabled' : '' }}>
                        <i data-feather="send" class="me-1"></i> إرسال الرسالة
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection