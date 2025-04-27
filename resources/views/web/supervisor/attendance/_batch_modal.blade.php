{{-- Modal لتسجيل الحضور الجماعي (للمشرف) --}}
{{-- تأكد من أن متغير $supervisedClasses متاح في الـ view الذي يتضمن هذا الملف --}}
@if(isset($supervisedClasses) && !$supervisedClasses->isEmpty())
<div class="modal fade" id="batchAttendanceModalSupervisor" tabindex="-1" aria-labelledby="batchAttendanceModalLabelSupervisor" aria-hidden="true">
  <div class="modal-dialog">
    {{-- توجيه إلى المسار الذي يعرض النموذج الخاص بالمشرف --}}
    <form method="GET" action="{{ route('supervisor.attendance.createBatchForm') }}">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="batchAttendanceModalLabelSupervisor">اختيار الفصل والتاريخ</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label for="batch_date_sup_modal" class="form-label">التاريخ:</label>
                <input type="date" class="form-control" id="batch_date_sup_modal" name="date" value="{{ $selectedDate ?? now()->format('Y-m-d') }}" required>
            </div>
             <div class="mb-3">
                <label for="batch_class_id_sup_modal" class="form-label">الفصل الدراسي:</label>
                <select class="form-select" id="batch_class_id_sup_modal" name="class_id" required>
                    <option value="" disabled selected>-- اختر الفصل --</option>
                    @foreach($supervisedClasses as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
          <button type="submit" class="btn btn-primary">عرض نموذج التسجيل</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endif