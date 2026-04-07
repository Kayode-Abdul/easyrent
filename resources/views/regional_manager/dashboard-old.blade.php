@extends('layout')
@section('content')
<div class="content">
    <h3 class="mb-3">Regional Manager Dashboard</h3>
    <div class="card mb-4">
        <div class="card-header">Assigned Regions</div>
        <div class="card-body">
            @php($__scopes = isset($scopes) ? $scopes : collect())
            @if($__scopes->count())
                <ul class="mb-0">
                    @foreach($__scopes as $s)
                        <li>{{ $s->state }}@if($s->lga) / {{ $s->lga }} @else (All LGAs) @endif</li>
                    @endforeach
                </ul>
            @else
                <div class="alert alert-warning mb-0">No regions assigned. Contact admin.</div>
            @endif
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Properties In Region</h5>
                    <div class="display-4">{{ $propertyCount ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Marketers</h5>
                    <div class="display-4">--</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Pending Approvals</h5>
                    <div class="display-4">--</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
