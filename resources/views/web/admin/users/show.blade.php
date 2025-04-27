@extends('layouts.admin')

@section('title', 'ملف المستخدم: ' . $user->name)

@section('header-buttons')
    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-warning me-2">
        <i data-feather="edit-2" class="me-1"></i> تعديل المستخدم
    </a>
    @if(Auth::id() !== $user->id) {{-- لا تسمح بحذف المستخدم لنفسه --}}
        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا المستخدم؟ سيتم حذف ملفه الشخصي المرتبط أيضًا.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger me-2">
                <i data-feather="trash-2" class="me-1"></i> حذف المستخدم
            </button>
        </form>
    @endif
    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary">
        <i data-feather="arrow-left" class="me-1"></i> العودة إلى القائمة
    </a>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        {{-- معلومات المستخدم الأساسية --}}
        <div class="col-lg-6 mb-4">
             <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i data-feather="user" class="me-1"></i> معلومات الحساب</h5>
                </div>
                <div class="card-body">
                     <dl class="row mb-0">
                        <dt class="col-sm-4">الاسم الكامل:</dt>
                        <dd class="col-sm-8">{{ $user->name }}</dd>

                        <dt class="col-sm-4">البريد الإلكتروني:</dt>
                        <dd class="col-sm-8">{{ $user->email }}</dd>

                         <dt class="col-sm-4">الدور:</dt>
                         <dd class="col-sm-8">
                            @php
                                $roleBadge = match($user->role) { 'Admin' => 'danger', 'Parent' => 'info', 'Supervisor' => 'warning', default => 'secondary' };
                                $roles = ['Admin' => 'مدير', 'Parent' => 'ولي أمر', 'Supervisor' => 'مشرف'];
                                $roleText = $roles[$user->role] ?? $user->role;
                            @endphp
                            <span class="badge bg-{{ $roleBadge }}">{{ $roleText }}</span>
                        </dd>

                         <dt class="col-sm-4">الحالة:</dt>
                        <dd class="col-sm-8">
                            @if($user->is_active)
                                <span class="badge bg-success">نشط</span>
                            @else
                                <span class="badge bg-danger">غير نشط</span>
                            @endif
                        </dd>

                         <dt class="col-sm-4">تاريخ الإنشاء:</dt>
                        <dd class="col-sm-8">{{ $user->created_at->format('Y-m-d H:i A') }}</dd>

                         <dt class="col-sm-4">آخر تحديث:</dt>
                        <dd class="col-sm-8">{{ $user->updated_at->format('Y-m-d H:i A') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        {{-- معلومات الملف الشخصي المرتبط (ولي أمر أو مدير) --}}
         <div class="col-lg-6 mb-4">
             <div class="card h-100">
                <div class="card-header">
                     <h5 class="card-title mb-0"><i data-feather="info" class="me-1"></i> معلومات الملف الشخصي الإضافية</h5>
                </div>
                <div class="card-body">
                    @if($user->role === 'Admin' && $user->adminProfile)
                         <dl class="row mb-0">
                            <dt class="col-sm-4">بريد الاتصال:</dt>
                            <dd class="col-sm-8">{{ $user->adminProfile->contact_email ?: '-' }}</dd>
                            <dt class="col-sm-4">هاتف الاتصال:</dt>
                            <dd class="col-sm-8">{{ $user->adminProfile->contact_phone ?: '-' }}</dd>
                         </dl>
                     @elseif($user->role === 'Parent' && $user->parentProfile)
                          <dl class="row mb-0">
                            <dt class="col-sm-4">بريد الاتصال:</dt>
                            <dd class="col-sm-8">{{ $user->parentProfile->contact_email ?: '-' }}</dd>
                            <dt class="col-sm-4">هاتف الاتصال:</dt>
                            <dd class="col-sm-8">{{ $user->parentProfile->contact_phone ?: '-' }}</dd>
                             <dt class="col-sm-4">العنوان:</dt>
                            <dd class="col-sm-8">{{ $user->parentProfile->address ?: '-' }}</dd>
                             <dt class="col-sm-4">الأطفال المرتبطون:</dt>
                             <dd class="col-sm-8">
                                 @forelse ($user->parentProfile->children as $child)
                                     <a href="{{ route('admin.children.show', $child) }}" class="me-2">{{ $child->full_name }}</a>
                                 @empty
                                    لا يوجد أطفال مرتبطون.
                                 @endforelse
                             </dd>
                         </dl>
                    @elseif($user->role === 'Supervisor' /* && $user->supervisorProfile */)
                        <p class="text-muted">لا توجد بيانات ملف شخصي إضافية للمشرف حاليًا.</p>
                    @else
                        <p class="text-muted">لا توجد بيانات ملف شخصي إضافية لهذا الدور.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

     {{-- يمكنك إضافة أقسام أخرى هنا لعرض أنشطة المستخدم (مثل الإعلانات التي أنشأها المدير، إلخ) --}}

</div>
@endsection