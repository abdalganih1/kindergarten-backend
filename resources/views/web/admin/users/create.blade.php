@extends('layouts.admin')

@section('title', 'إضافة مستخدم جديد')

@section('content')
<div class="container-fluid">
     <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">إضافة مستخدم جديد</h1>
         <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
            <i data-feather="arrow-right" class="me-1"></i> العودة إلى القائمة
        </a>
    </div>

    <div class="card">
         <div class="card-body">
             <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf

                {{-- حقل الاسم --}}
                <div class="mb-3">
                    <label for="name" class="form-label">الاسم الكامل <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                 {{-- حقل البريد الإلكتروني --}}
                <div class="mb-3">
                    <label for="email" class="form-label">البريد الإلكتروني (لتسجيل الدخول) <span class="text-danger">*</span></label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                     @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                 <div class="row">
                    {{-- حقل كلمة المرور --}}
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">كلمة المرور <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                         @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                     {{-- حقل تأكيد كلمة المرور --}}
                    <div class="col-md-6 mb-3">
                         <label for="password_confirmation" class="form-label">تأكيد كلمة المرور <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>
                </div>

                <div class="row">
                     {{-- حقل الدور --}}
                    <div class="col-md-6 mb-3">
                        <label for="role" class="form-label">الدور <span class="text-danger">*</span></label>
                        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                             <option value="" disabled {{ old('role') ? '' : 'selected' }}>-- اختر الدور --</option>
                             @foreach($roles as $value => $label)
                                <option value="{{ $value }}" {{ old('role') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- حقل الحالة --}}
                    <div class="col-md-6 mb-3 align-self-center">
                         <div class="form-check form-switch mt-3">
                          <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}> {{-- افتراضي نشط --}}
                          <label class="form-check-label" for="is_active">الحساب نشط؟</label>
                        </div>
                         @error('is_active') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- حقول إضافية لملف ولي الأمر (تظهر شرطيًا) --}}
                <div id="parent-fields" style="{{ old('role') === 'Parent' ? '' : 'display: none;' }}">
                    <h5 class="mt-4 mb-3 border-top pt-3">بيانات ولي الأمر الإضافية (اختياري)</h5>
                     <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="parent_contact_email" class="form-label">بريد الاتصال (غير تسجيل الدخول)</label>
                            <input type="email" class="form-control @error('parent_contact_email') is-invalid @enderror" id="parent_contact_email" name="parent_contact_email" value="{{ old('parent_contact_email') }}">
                            @error('parent_contact_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                             <label for="parent_contact_phone" class="form-label">هاتف الاتصال</label>
                             <input type="text" class="form-control @error('parent_contact_phone') is-invalid @enderror" id="parent_contact_phone" name="parent_contact_phone" value="{{ old('parent_contact_phone') }}">
                             @error('parent_contact_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                     </div>
                     <div class="mb-3">
                         <label for="parent_address" class="form-label">العنوان</label>
                         <textarea class="form-control @error('parent_address') is-invalid @enderror" id="parent_address" name="parent_address" rows="2">{{ old('parent_address') }}</textarea>
                         @error('parent_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                     </div>
                </div>

                {{-- حقول إضافية لملف المدير (تظهر شرطيًا) --}}
                 <div id="admin-fields" style="{{ old('role') === 'Admin' ? '' : 'display: none;' }}">
                    <h5 class="mt-4 mb-3 border-top pt-3">بيانات المدير الإضافية (اختياري)</h5>
                     <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="admin_contact_email" class="form-label">بريد الاتصال (غير تسجيل الدخول)</label>
                            <input type="email" class="form-control @error('admin_contact_email') is-invalid @enderror" id="admin_contact_email" name="admin_contact_email" value="{{ old('admin_contact_email') }}">
                            @error('admin_contact_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                             <label for="admin_contact_phone" class="form-label">هاتف الاتصال</label>
                             <input type="text" class="form-control @error('admin_contact_phone') is-invalid @enderror" id="admin_contact_phone" name="admin_contact_phone" value="{{ old('admin_contact_phone') }}">
                             @error('admin_contact_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                     </div>
                </div>

                {{-- أضف حقول المشرف هنا بنفس الطريقة إذا لزم الأمر --}}

                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary me-2">إلغاء</a>
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save" class="me-1"></i> حفظ المستخدم
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // إظهار/إخفاء الحقول الإضافية بناءً على الدور المختار
    const roleSelect = document.getElementById('role');
    const parentFields = document.getElementById('parent-fields');
    const adminFields = document.getElementById('admin-fields');
    // const supervisorFields = document.getElementById('supervisor-fields'); // إذا وُجد

    function toggleProfileFields() {
        const selectedRole = roleSelect.value;
        if (parentFields) parentFields.style.display = selectedRole === 'Parent' ? 'block' : 'none';
        if (adminFields) adminFields.style.display = selectedRole === 'Admin' ? 'block' : 'none';
        // if (supervisorFields) supervisorFields.style.display = selectedRole === 'Supervisor' ? 'block' : 'none';
    }

    // التشغيل عند تغيير القيمة وعند تحميل الصفحة
    roleSelect.addEventListener('change', toggleProfileFields);
    document.addEventListener('DOMContentLoaded', toggleProfileFields);
</script>
@endpush