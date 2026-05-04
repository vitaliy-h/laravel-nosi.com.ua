@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Feedback</h1>

        <form method="POST" action="{{ route('feedback.save') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Message</label>
                <textarea name="message" class="form-control"></textarea>
            </div>

            <button class="btn btn-success">Send</button>
        </form>
    </div>
@endsection
