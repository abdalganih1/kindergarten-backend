@extends('layouts.admin')

@section('title', 'إدارة المستخدمين')

@section('header-buttons')
<a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-success">
    <i data-feather="user-plus" class="me-1"></i> إضافة مستخدم جديد
</a>
@endsection

@section('content')
<div class="container-fluid">

    {{-- قسم الفلترة والبحث --}}
    <div class="card mb-4">
        <div class="card-header">
           <i data-feather="filter" class="me-1"></i> فلترة وبحث المستخدمين
        </div>
        <div class="card-body">
             <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3 align-items-end">
                {{-- حقل البحث --}}
                <div class="col-md-4">
                    <label for="search" class="form-label">بحث بالاسم/البريد:</label>
                    <input type="text" class="form-control form-control-sm" id="search" name="search" placeholder="اسم أو بريد إلكتروني..." value="{{ $searchTerm ?? '' }}">
                </div>

                {{-- فلتر الدور --}}
                <div class="col-md-3">
                    <label for="role" class="form-label">الدور:</label>
                    <select class="form-select form-select-sm" id="role" name="role">
                        <option value="">-- الكل --</option>
                        @foreach($roles as $value => $label)
                            <option value="{{ $value }}" {{ $role == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                 {{-- فلتر الحالة --}}
                <div class="col-md-2">
                    <label for="status" class="form-label">الحالة:</label>
                    <select class="form-select form-select-sm" id="status" name="status">
                        <option value="">-- الكل --</option>
                        <option value="active" {{ $status == 'active' ? 'selected' : '' }}>نشط</option>
                        <option value="inactive" {{ $status == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                    </select>
                </div>

                {{-- زر تطبيق الفلتر --}}
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-sm w-100">بحث</button>
                </div>
            </form>
        </div>
    </div>


    {{-- جدول عرض المستخدمين --}}
    <div class="card">
         <div class="card-header">
            <h5 class="card-title mb-0">قائمة المستخدمين</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>الاسم</th>
                            <th>البريد الإلكتروني</th>
                            <th>الدور</th>
                            <th>الحالة</th>
                            <th>تاريخ الإنشاء</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $index => $user)
                        <tr>
                            <td>{{ $users->firstItem() + $index }}</td>
                            <td>
                                <a href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                 @php
                                    $roleBadge = match($user->role) { 'Admin' => 'danger', 'Parent' => 'info', 'Supervisor' => 'warning', default => 'secondary' };
                                    $roleText = $roles[$user->role] ?? $user->role;
                                @endphp
                                <span class="badge bg-{{ $roleBadge }}">{{ $roleText }}</span>
                            </td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge bg-success">نشط</span>
                                @else
                                    <span class="badge bg-danger">غير نشط</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at->format('Y-m-d') }}</td>
                            <td>
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-info me-1" title="عرض التفاصيل">
                                    <i data-feather="eye"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-warning me-1" title="تعديل">
                                    <i data-feather="edit-2"></i>
                                </a>
                                @if(Auth::id() !== $user->id) {{-- لا تسمح بحذف المستخدم لنفسه --}}
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا المستخدم؟ سيتم حذف ملفه الشخصي المرتبط أيضًا.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                            <i data-feather="trash-2"></i>
                                        </button>
                                    </form>
                                @else
                                     <button type="button" class="btn btn-sm btn-secondary" title="لا يمكن حذف حسابك" disabled>
                                            <i data-feather="trash-2"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">لا يوجد مستخدمون يطابقون الفلترة الحالية.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
         @if ($users->hasPages())
            <div class="card-footer">
                {{ $users->links() }}
            </div>
        @endif
    </div> {{-- نهاية الـ card --}}
</div>
@endsection