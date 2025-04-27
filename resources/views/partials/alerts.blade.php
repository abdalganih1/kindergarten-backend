@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <span data-feather="check-circle" class="me-1 align-text-bottom"></span>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
     <div class="alert alert-danger alert-dismissible fade show" role="alert">
         <span data-feather="alert-circle" class="me-1 align-text-bottom"></span>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
         <h6 class="alert-heading">
             <span data-feather="alert-triangle" class="me-1 align-text-bottom"></span>
             حدث خطأ! يرجى مراجعة الحقول التالية:
        </h6>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif