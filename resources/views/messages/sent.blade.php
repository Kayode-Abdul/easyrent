@extends('layout')
@section('content')
<div class="content">
    <h2>Sent Messages</h2>
    <table class="table">
        <thead>
            <tr>
                <th>To</th>
                <th>Subject</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
        @forelse($messages as $message)
            <tr>
                <td>{{ $message->receiver->username ?? 'Unknown' }}</td>
                <td><a href="{{ route('messages.show', $message->id) }}">{{ $message->subject ?? '(No Subject)' }}</a></td>
                <td>{{ $message->created_at->format('d M Y H:i') }}</td>
            </tr>
        @empty
            <tr><td colspan="3" class="text-muted">No sent messages.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
