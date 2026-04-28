@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="alert alert-info mb-3" role="alert">Quiz management layout loaded (debug)</div>
    <h1 class="mb-3">Quiz Management</h1>
    <p class="text-muted mb-3">Content will load below via Livewire. If not, see the debug banner.</p>
    <div class="card mb-3">
        <div class="card-body">
            Nội dung tĩnh xác nhận layout đang render đúng. Nếu nội dung này hiển thị nhưng phần Livewire vẫn trống, vấn đề là dữ liệu hoặc Livewire render.
        </div>
    </div>
    @livewire('quiz')
</div>
@endsection
