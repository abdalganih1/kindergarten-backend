@extends('layouts.admin')

@section('title', 'صندوق الرسائل')
{{-- إضافة زر إنشاء رسالة جديدة --}}
@section('header-buttons')
<a href="{{ route('admin.messages.create') }}" class="btn btn-sm btn-success">
    <i data-feather="plus-circle" class="me-1"></i> رسالة جديدة
</a>
@endsection
@section('content')
<div class="container-fluid">
    {{-- قسم الفلترة والبحث --}}
    <div class="card mb-4">
        <div class="card-header">
           <i data-feather="filter" class="me-1"></i> فلترة وبحث الرسائل
        </div>
        <div class="card-body">
             <form method="GET" action="{{ route('admin.messages.index') }}" class="row g-3 align-items-end">
                {{-- حقل البحث --}}
                <div class="col-md-3">
                    <label for="search" class="form-label">بحث بالموضوع/المحتوى:</label>
                    <input type="text" class="form-control form-control-sm" id="search" name="search" placeholder="كلمة مفتاحية..." value="{{ $searchTerm ?? '' }}">
                </div>

                {{-- فلتر المرسل --}}
                <div class="col-md-2">
                    <label for="sender_id" class="form-label">المرسل:</label>
                    <select class="form-select form-select-sm" id="sender_id" name="sender_id">
                        <option value="">-- الكل --</option>
                        @foreach($users as $id => $name)
                            <option value="{{ $id }}" {{ $senderId == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- فلتر المستقبل --}}
                <div class="col-md-2">
                    <label for="recipient_id" class="form-label">المستقبل:</label>
                     <select class="form-select form-select-sm" id="recipient_id" name="recipient_id">
                        <option value="">-- الكل --</option>
                         @foreach($users as $id => $name)
                            <option value="{{ $id }}" {{ $recipientId == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                 {{-- فلتر حالة القراءة (لرسائل المدير الواردة) --}}
                <div class="col-md-2">
                    <label for="read_status" class="form-label">حالة القراءة (الواردة لي):</label>
                    <select class="form-select form-select-sm" id="read_status" name="read_status">
                        <option value="">-- الكل --</option>
                        <option value="unread" {{ $readStatus == 'unread' ? 'selected' : '' }}>غير مقروءة</option>
                        <option value="read" {{ $readStatus == 'read' ? 'selected' : '' }}>مقروءة</option>
                    </select>
                </div>


                {{-- زر تطبيق الفلتر --}}
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-sm w-100">بحث / عرض</button>
                </div>
            </form>
        </div>
    </div>

    {{-- جدول عرض الرسائل --}}
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">الرسائل</h5>
             {{-- يمكنك إضافة زر لإنشاء رسالة جديدة إذا أردت تفعيل دالة store للمدير --}}
             {{-- <a href="{{ route('admin.messages.create') }}" class="btn btn-sm btn-success float-end">
                <i data-feather="plus-circle" class="me-1"></i> رسالة جديدة
             </a> --}}
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
                            <th>بداية الرسالة</th>
                            <th>تاريخ الإرسال</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($messages as $index => $message)
                         @php
                            // تحديد إذا كانت الرسالة واردة للمدير الحالي وغير مقروءة
                            $isUnreadForAdmin = ($message->recipient_id === Auth::id() && is_null($message->read_at));
                         @endphp
                        <tr class="{{ $isUnreadForAdmin ? 'table-warning' : '' }}"> {{-- تمييز الرسائل غير المقروءة --}}
                            <td>{{ $messages->firstItem() + $index }}</td>
                            <td>{{ $message->sender->name ?? 'N/A' }} <small class="text-muted">({{ $message->sender->role ?? '' }})</small></td>
                            <td>{{ $message->recipient->name ?? 'N/A' }} <small class="text-muted">({{ $message->recipient->role ?? '' }})</small></td>
                            <td>
                                <a href="{{ route('admin.messages.show', $message) }}">
                                    {{ $message->subject ?: '(بدون موضوع)' }}
                                </a>
                            </td>
                            <td>{{ Str::limit($message->body, 50) }}</td>
                            <td>{{ $message->sent_at->format('Y-m-d H:i') }} <small class="text-muted">({{ $message->sent_at->diffForHumans() }})</small></td>
                            <td>
                                @if($message->recipient_id === Auth::id()) {{-- إذا كانت رسالة واردة للمدير --}}
                                    @if($message->read_at)
                                        <span class="badge bg-success">مقروءة</span>
                                    @else
                                        <span class="badge bg-warning text-dark">غير مقروءة</span>
                                    @endif
                                @else
                                     <span class="badge bg-secondary">مرسلة</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.messages.show', $message) }}" class="btn btn-sm btn-info me-1" title="عرض الرسالة">
                                    <i data-feather="eye"></i>
                                </a>
                                <form action="{{ route('admin.messages.destroy', $message) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه الرسالة نهائيًا؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                        <i data-feather="trash-2"></i>
                                    </button>
                                </form>
                                {{-- يمكنك إضافة زر رد هنا يفتح نموذجًا أو صفحة جديدة --}}
                                {{-- <a href="#" class="btn btn-sm btn-outline-primary ms-1" title="رد"><i data-feather="corner-up-left"></i></a> --}}
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