@extends('layouts.supervisor') {{-- أو layouts.admin --}}

@section('title', 'صندوق الرسائل')

@section('header-buttons')
<a href="{{ route('supervisor.messages.create') }}" class="btn btn-sm btn-success">
    <i data-feather="plus-circle" class="me-1"></i> رسالة جديدة
</a>
@endsection

@section('content')
<div class="container-fluid">

    {{-- قسم الفلترة --}}
    <div class="card mb-4">
        <div class="card-header">
           <i data-feather="filter" class="me-1"></i> فلترة الرسائل
        </div>
        <div class="card-body">
             <form method="GET" action="{{ route('supervisor.messages.index') }}" class="row g-3 align-items-end">
                {{-- حقل البحث --}}
                <div class="col-md-4">
                    <label for="search" class="form-label">بحث بالموضوع/المحتوى:</label>
                    <input type="text" class="form-control form-control-sm" id="search" name="search" placeholder="كلمة مفتاحية..." value="{{ $searchTerm ?? '' }}">
                </div>

                {{-- فلتر جهة الاتصال (مرسل أو مستقبل آخر) --}}
                <div class="col-md-3">
                    <label for="contact_id" class="form-label">جهة الاتصال:</label>
                    <select class="form-select form-select-sm" id="contact_id" name="contact_id">
                        <option value="">-- الكل --</option>
                        @foreach($contactList as $id => $name)
                            <option value="{{ $id }}" {{ $contactId == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                 {{-- فلتر حالة القراءة (للوارد) --}}
                <div class="col-md-3">
                    <label for="read_status" class="form-label">حالة القراءة (الواردة):</label>
                    <select class="form-select form-select-sm" id="read_status" name="read_status">
                        <option value="">-- الكل --</option>
                        <option value="unread" {{ $readStatus == 'unread' ? 'selected' : '' }}>غير مقروءة</option>
                        <option value="read" {{ $readStatus == 'read' ? 'selected' : '' }}>مقروءة</option>
                    </select>
                </div>

                {{-- زر تطبيق الفلتر --}}
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">بحث</button>
                </div>
            </form>
        </div>
    </div>

    {{-- جدول عرض الرسائل --}}
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">الرسائل</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>المرسل</th>
                            <th>المستقبل</th>
                            <th>الموضوع</th>
                            <th>تاريخ الإرسال</th>
                            <th>الحالة (الوارد)</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($messages as $index => $message)
                         @php $isUnread = ($message->recipient_id === Auth::id() && is_null($message->read_at)); @endphp
                        <tr class="{{ $isUnread ? 'table-warning' : '' }}"> {{-- تمييز غير المقروء --}}
                            <td>{{ $messages->firstItem() + $index }}</td>
                            <td>
                                @if($message->sender_id === Auth::id())
                                   <strong>أنا</strong>
                                @else
                                    {{ $message->sender->name ?? 'N/A' }}
                                    <small class="text-muted">({{ $message->sender->role ?? '' }})</small>
                                @endif
                            </td>
                             <td>
                                @if($message->recipient_id === Auth::id())
                                   <strong>أنا</strong>
                                @else
                                    {{ $message->recipient->name ?? 'N/A' }}
                                    <small class="text-muted">({{ $message->recipient->role ?? '' }})</small>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('supervisor.messages.show', $message) }}">
                                    {{ $message->subject ?: Str::limit($message->body, 30) }}
                                </a>
                            </td>
                            <td>{{ $message->sent_at->diffForHumans() }}</td>
                            <td>
                                @if($message->recipient_id === Auth::id())
                                    @if($message->read_at) <span class="badge bg-light text-dark border">مقروءة</span> @else <span class="badge bg-warning text-dark">غير مقروءة</span> @endif
                                @else
                                     <span class="badge bg-secondary">مرسلة</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('supervisor.messages.show', $message) }}" class="btn btn-sm btn-info me-1" title="عرض الرسالة">
                                    <i data-feather="eye"></i>
                                </a>
                                <form action="{{ route('supervisor.messages.destroy', $message) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه الرسالة؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                        <i data-feather="trash-2"></i>
                                    </button>
                                </form>
                                {{-- زر الرد قد يوجه لصفحة الإنشاء مع ملء حقل المستقبل والموضوع --}}
                                @if($message->sender_id !== Auth::id())
                                <a href="{{ route('supervisor.messages.create', ['reply_to' => $message->sender_id, 'subject' => 'RE: ' . $message->subject]) }}" class="btn btn-sm btn-outline-primary ms-1" title="رد"><i data-feather="corner-up-left"></i></a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">لا توجد رسائل تطابق الفلترة الحالية.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
         @if ($messages->hasPages())
            <div class="card-footer">
                {{ $messages->links() }}
            </div>
        @endif
    </div> {{-- نهاية الـ card --}}
</div>
@endsection