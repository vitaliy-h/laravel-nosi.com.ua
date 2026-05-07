@if(session()->has('success'))
    <div class="card card-body py-3 alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if(session()->has('error'))
    <div class="card card-body py-3 alert alert-danger">
        {{ session('error') }}
    </div>
@endif
