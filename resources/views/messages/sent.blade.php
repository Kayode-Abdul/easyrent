@extends('layout')
@section('content')
<div class="content">
    <h2>Sent Messages</h2>
    <table class="table datatable" id="sent-table">
        <thead>
            <tr>
                <th>To</th>
                <th>Subject</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($messages ?? [] as $message)
            <tr>
                <td>{{ $message->receiver->username ?? 'Unknown' }}</td>
                <td><a href="{{ route('messages.show', $message->id) }}">{{ $message->subject ?? '(No Subject)' }}</a>
                </td>
                <td>{{ $message->created_at->format('d M Y H:i') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="text-muted">No sent messages.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script>
    $(document).ready(function () {
        $('#sent-table').DataTable({
            "order": [[2, "desc"]], // Sort by date column
            "pageLength": 25,
            "responsive": true
        });
    });
</script>
@endpush