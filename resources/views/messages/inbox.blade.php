@extends('layout')
@section('content')
<div class="content">
    <h2>Inbox</h2>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <table class="table">
        <thead>
            <tr>
                <th>From</th>
                <th>Subject</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        @forelse($messages as $message)
            <tr>
                <td>{{ $message->sender->username ?? 'Unknown' }}</td>
                <td><a href="{{ route('messages.show', $message->id) }}">{{ $message->subject ?? '(No Subject)' }}</a></td>
                <td>{{ $message->created_at->format('d M Y H:i') }}</td>
                <td>{!! $message->is_read ? '<span class="badge badge-success">Read</span>' : '<span class="badge badge-warning">Unread</span>' !!}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-muted">No messages.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
