@extends('layouts.supervisor') {{-- أو layouts.admin --}}

@section('title', 'إدارة الوسائط (الصور والفيديو)')

@section('header-buttons')
<a href="{{ route('supervisor.media.create') }}" class="btn btn-sm btn-success">
    <i data-feather="upload" class="me-1"></i> رفع وسائط جديدة
</a>
@endsection

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">معرض الوسائط (فصولي)</h2>

     @if(isset($noClassesAssigned) && $noClassesAssigned)
        <div class="alert alert-warning" role="alert">
           <i data-feather="alert-circle" class="me-1 align-text-bottom"></i>
           لم يتم تعيين أي فصول دراسية لك حتى الآن.
        </div>
    @else
        {{-- قسم الفلترة --}}
        <div class="card mb-4">
            <div class="card-header">
               <i data-feather="filter" class="me-1"></i> فلترة الوسائط
            </div>
            <div class="card-body">
                 <form method="GET" action="{{ route('supervisor.media.index') }}" class="row g-3 align-items-end">
                    {{-- فلتر نوع الوسائط --}}
                    <div class="col-md-4">
                        <label for="media_type" class="form-label">نوع الوسائط:</label>
                        <select class="form-select form-select-sm" id="media_type" name="media_type" onchange="this.form.submit()">
                            <option value="">-- الكل --</option>
                            @foreach($mediaTypes as $value => $label)
                                <option value="{{ $value }}" {{ $mediaType == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- فلتر الفصل (فصول المشرف فقط) --}}
                    <div class="col-md-4">
                        <label for="class_id" class="form-label">الفصل الدراسي:</label>
                        <select class="form-select form-select-sm" id="class_id" name="class_id" onchange="this.form.submit()">
                            <option value="">-- كل فصولي --</option>
                            <option value="none" {{ $classId === 'none' ? 'selected' : '' }}>غير مرتبط بفصل</option>
                            @foreach($supervisedClasses as $id => $name)
                                <option value="{{ $id }}" {{ $classId == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                     {{-- زر إعادة التعيين --}}
                     @if($mediaType || $classId)
                     <div class="col-md-4">
                         <a href="{{ route('supervisor.media.index') }}" class="btn btn-secondary btn-sm w-100">إعادة تعيين الفلاتر</a>
                     </div>
                     @endif

                </form>
            </div>
        </div>


        {{-- عرض الوسائط كشبكة (Grid) --}}
        <div class="row g-3">
            @forelse ($mediaItems as $item)
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
                    <div class="card h-100 shadow-sm">
                         {{-- عرض الصورة أو أيقونة الفيديو --}}
                         @if($item->media_type == 'Image')
                            <a href="{{ Storage::disk('public')->url($item->file_path) }}" data-bs-toggle="tooltip" title="عرض الصورة بالحجم الكامل" target="_blank">
                                 <img src="{{ Storage::disk('public')->url($item->file_path) }}" class="card-img-top" alt="{{ $item->description ?: 'صورة' }}" style="height: 200px; object-fit: cover;">
                            </a>
                        @elseif($item->media_type == 'Video')
                            <div class="position-relative text-center bg-dark" style="height: 200px; line-height: 200px;">
                                <i data-feather="video" class="text-white" style="width: 50px; height: 50px;"></i>
                                 <a href="{{ Storage::disk('public')->url($item->file_path) }}" target="_blank" class="stretched-link" title="مشاهدة الفيديو"></a>
                            </div>
                        @endif
                        <div class="card-body small pb-2">
                            <p class="card-text mb-1">{{ Str::limit($item->description ?: 'لا يوجد وصف', 50) }}</p>
                            <p class="card-text text-muted mb-1">
                                <small><i data-feather="user" class="feather-sm"></i> {{ $item->uploader->name ?? 'N/A' }} | <i data-feather="clock" class="feather-sm"></i> {{ $item->upload_date->diffForHumans() }}</small>
                            </p>
                            {{-- عرض الارتباطات --}}
                             <p class="card-text mb-0">
                                 @if($item->associatedClass)<span class="badge bg-info me-1"><i data-feather="layers" class="feather-xs"></i> {{ $item->associatedClass->class_name }}</span>@endif
                                 @if($item->associatedChild)<span class="badge bg-success me-1"><i data-feather="user" class="feather-xs"></i> {{ $item->associatedChild->full_name }}</span>@endif
                                 @if($item->associatedEvent)<span class="badge bg-warning text-dark me-1"><i data-feather="calendar" class="feather-xs"></i> {{ $item->associatedEvent->event_name }}</span>@endif
                                 @if(!$item->associatedClass && !$item->associatedChild && !$item->associatedEvent)<span class="badge bg-secondary">عام</span>@endif
                             </p>
                        </div>
                         {{-- أزرار التحكم (تظهر فقط إذا كان المشرف هو من رفع الملف أو يخص فصله) --}}
                        @if($item->uploader_id === Auth::id() || (isset($supervisedClasses) && $item->associated_class_id && $supervisedClasses->has($item->associated_class_id)))
                            <div class="card-footer bg-transparent border-top-0 pt-0 text-end">
                                <a href="{{ route('supervisor.media.edit', $item) }}" class="btn btn-sm btn-outline-warning py-0 px-1 me-1" title="تعديل الوصف/الربط">
                                    <i data-feather="edit-2" style="width: 14px; height: 14px;"></i>
                                </a>
                                <form action="{{ route('supervisor.media.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه الوسائط نهائيًا؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1" title="حذف">
                                        <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-warning text-center" role="alert">
                        لا توجد وسائط تطابق الفلترة الحالية أو لم يتم رفع أي وسائط بعد للفصول التي تشرف عليها.
                    </div>
                </div>
            @endforelse
        </div>

        {{-- روابط الـ Pagination --}}
        <div class="mt-4 d-flex justify-content-center">
            {{ $mediaItems->links() }}
        </div>
    @endif {{-- نهاية التحقق من وجود فصول للمشرف --}}
</div>
{{-- أضف أيقونات أصغر حجمًا إذا لزم الأمر --}}
<style> .feather-sm { width: 12px; height: 12px; vertical-align: text-bottom; margin-left: 2px; } .feather-xs { width: 10px; height: 10px; vertical-align: text-bottom; } </style>
@endsection

@push('scripts')
<script>
    // تفعيل tooltips (إذا استخدمتها)
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>
@endpush