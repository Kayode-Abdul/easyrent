@extends('dash')
@section('content')
<div class="content">
    <h3 class="mb-3">Pending Property Approvals</h3>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    @if($properties->count())
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Owner</th>
                <th>State</th>
                <th>LGA</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($properties as $p)
            <tr>
                <td>{{ $p->property_id }}</td>
                <td>{{ $p->title ?? 'N/A' }}</td>
                <td>{{ optional($p->user)->first_name }} {{ optional($p->user)->last_name }}</td>
                <td>{{ $p->state }}</td>
                <td>{{ $p->lga }}</td>
                <td>{{ optional($p->created_at)->format('Y-m-d') }}</td>
                <td>
                    <div class="d-flex gap-2">
                        <form action="{{ route('regional.property.approve', $p->property_id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success rounded-pill px-3">
                                <i class="nc-icon nc-check-2"></i> Approve
                            </button>
                        </form>
                        <form action="{{ route('regional.property.reject', $p->property_id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger rounded-pill px-3">
                                <i class="nc-icon nc-simple-remove"></i> Reject
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="alert alert-info">No pending properties found.</div>
    @endif
</div>
@endsection