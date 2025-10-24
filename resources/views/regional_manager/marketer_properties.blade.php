@extends('layout')
@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title">Properties by {{ $marketer->name }}</h4>
                            <p class="card-category">View and manage properties referred by this marketer</p>
                        </div>
                        <div>
                            <a href="{{ route('regional.marketers') }}" class="btn btn-sm btn-outline-primary">
                                <i class="fa fa-arrow-left"></i> Back to Marketers
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="text-primary">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Location</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Listed On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($properties as $property)
                                <tr>
                                    <td>{{ $property->prop_id }}</td>
                                    <td>{{ $property->title }}</td>
                                    <td>{{ ucfirst($property->property_type) }}</td>
                                    <td>{{ $property->location }}</td>
                                    <td>${{ number_format($property->price) }}</td>
                                    <td>
                                        @if($property->status == 'active')
                                            <span class="badge badge-success">Active</span>
                                        @elseif($property->status == 'pending')
                                            <span class="badge badge-warning">Pending</span>
                                        @elseif($property->status == 'suspended')
                                            <span class="badge badge-danger">Suspended</span>
                                        @else
                                            <span class="badge badge-secondary">{{ ucfirst($property->status) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $property->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                                Actions
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="{{ url('/dashboard/property/' . $property->prop_id) }}">View Details</a>
                                                
                                                @if($property->status != 'suspended')
                                                <form action="{{ route('regional.property.suspend', $property->prop_id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        Suspend
                                                    </button>
                                                </form>
                                                @else
                                                <form action="{{ route('regional.property.activate', $property->prop_id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-success">
                                                        Activate
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No properties found for this marketer</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-center mt-4">
                        {{ $properties->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection