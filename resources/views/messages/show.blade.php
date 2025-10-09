@extends('layout')
@section('content')
<div class="content">
    <h2>Message Details</h2>
    <div class="card mb-3">
        <div class="card-header">
            <strong>From:</strong>
            @if($message->sender && $message->sender->photo)
                <img src="{{ asset('storage/' . $message->sender->photo) }}" alt="{{ $message->sender->username }}'s photo" class="rounded-circle me-1" style="width:32px;height:32px;object-fit:cover;vertical-align:middle;">
            @endif
            {{ $message->sender->username ?? 'Unknown' }}<br>
            <strong>To:</strong>
            @if($message->receiver && $message->receiver->photo)
                <img src="{{ asset('storage/' . $message->receiver->photo) }}" alt="{{ $message->receiver->username }}'s photo" class="rounded-circle me-1" style="width:32px;height:32px;object-fit:cover;vertical-align:middle;">
            @endif
            {{ $message->receiver->username ?? 'Unknown' }}<br>
            <strong>Subject:</strong> {{ $message->subject ?? '(No Subject)' }}<br>
            <strong>Date:</strong> {{ $message->created_at->format('d M Y H:i') }}
        </div>
        <div class="card-body">
            <p>{!! $message->body !!}</p>
        </div>
    </div>
    <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
</div>
@endsection
