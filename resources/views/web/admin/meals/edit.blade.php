@extends('layouts.admin')

@section('title', 'تعديل وجبة')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">تعديل وجبة ليوم: {{ \Carbon\Carbon::parse($meal->meal_date)->format('Y-m-d') }}</h1>
        <a href="{{ route('admin.meals.index', ['date' => $meal->meal_date]) }}" class="btn btn-outline-secondary">
            <i data-feather="arrow-right" class="me-1"></i> العودة إلى القائمة
        </a>
    </div>

     <div class="card">
        <div class="card-body">
             <form action="{{ route('admin.meals.update', $meal) }}" method="POST">
                @csrf
                @method('PUT')

                 {{-- حقل التاريخ --}}
                <div class="mb-3">
                    <label for="meal_date" class="form-label">تاريخ الوجبة <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('meal_date') is-invalid @enderror" id="meal_date" name="meal_date" value="{{ old('meal_date', $meal->meal_date) }}" required>
                    @error('meal_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- حقل نوع الوجبة --}}
                <div class="mb-3">
                    <label for="meal_type" class="form-label">نوع الوجبة <span class="text-danger">*</span></label>
                    <select class="form-select @error('meal_type') is-invalid @enderror" id="meal_type" name="meal_type" required>
                        {{-- <option value="" disabled>-- اختر النوع --</option> --}}
                        @foreach($mealTypes as $value => $label)
                            <option value="{{ $value }}" {{ old('meal_type', $meal->meal_type) == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                     @error('meal_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                 {{-- حقل وصف الوجبة --}}
                 <div class="mb-3">
                    <label for="menu_description" class="form-label">الوصف / القائمة <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('menu_description') is-invalid @enderror" id="menu_description" name="menu_description" rows="5" required>{{ old('menu_description', $meal->menu_description) }}</textarea>
                    @error('menu_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- حقل الفصل المخصص (اختياري) --}}
                 <div class="mb-3">
                    <label for="class_id" class="form-label">تخصيص لفصل (اختياري)</label>
                    <select class="form-select @error('class_id') is-invalid @enderror" id="class_id" name="class_id">
                        <option value="">-- وجبة عامة (لكل الفصول) --</option>
                         @foreach($classes as $id => $name)
                            <option value="{{ $id }}" {{ old('class_id', $meal->class_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('class_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="form-text text-muted">إذا تركت هذا الحقل فارغًا، ستكون الوجبة متاحة لجميع الفصول.</small>
                </div>


                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('admin.meals.index', ['date' => $meal->meal_date]) }}" class="btn btn-outline-secondary me-2">إلغاء</a>
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save" class="me-1"></i> حفظ التعديلات
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection