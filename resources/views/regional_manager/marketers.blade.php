@extends('layout')

@section('content')
<div class="content">
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h4 class="card-title">Marketers</h4>
        </div>
        <div class="card-body">
          @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif
          @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
          @endif

          @if(isset($marketers) && $marketers->count())
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($marketers as $idx => $m)
                  <tr>
                    <td>{{ $marketers->firstItem() + $idx }}</td>
                    <td>{{ $m->first_name }} {{ $m->last_name }}</td>
                    <td>{{ $m->email }}</td>
                    <td>{{ $m->phone }}</td>
                    <td>
                      <a href="{{ route('regional.marketer.properties', ['id' => $m->user_id]) }}" class="btn btn-sm btn-info">Referred Properties</a>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            {{ $marketers->links() }}
          @else
            <p class="text-muted mb-0">No marketers found.</p>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
