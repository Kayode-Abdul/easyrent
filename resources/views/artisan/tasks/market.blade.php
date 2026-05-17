@extends('layout')

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4 class="card-title">Artisan Marketplace</h4>
                        <div class="category">Current opportunities for your category</div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @forelse($tasks as $task)
                            <div class="col-md-4">
                                <div class="card card-user border">
                                    <div class="card-body">
                                        <div class="author">
                                            <h5 class="title text-primary">
                                                @if(auth()->user() && auth()->user()->artisan_category_id == $task->complaint->category_id)
                                                    <span class="badge badge-success px-2 py-1 mb-2 font-weight-bold" style="font-size: 0.75rem;"><i class="fa fa-star"></i> Matches Your Specialty</span><br>
                                                @endif
                                                {{ $task->complaint->title }}
                                            </h5>
                                            <p class="description">
                                                <i class="nc-icon nc-pin-3"></i> {{ $task->landlord->city ?? 'Location
                                                not specified' }}
                                            </p>
                                        </div>
                                        <p class="description text-center">
                                            {{ Str::limit($task->description, 100) }}
                                        </p>
                                        <div class="text-center mt-3">
                                            @if($task->status == 'assigned')
                                                <span class="badge badge-secondary p-2"><i class="fa fa-lock"></i> Assigned to an Artisan</span>
                                            @else
                                                <span class="badge badge-warning">Budget: {{ format_money($task->budget_min) }} - {{ format_money($task->budget_max) }}</span>
                                                <span class="badge badge-info">{{ $task->duration }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <hr>
                                        <div class="button-container text-center">
                                            @if($task->status == 'assigned')
                                                <button class="btn btn-secondary btn-round" disabled>Closed for Bidding</button>
                                            @else
                                                <a href="{{ route('artisan.tasks.show', $task) }}" class="btn btn-primary btn-round">View Detail & Bid</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-md-12 text-center py-5">
                                <i class="nc-icon nc-zoom-split" style="font-size: 3rem; opacity: 0.3;"></i>
                                <h4 class="mt-3 text-muted">No open tasks found in the marketplace.</h4>
                                <p>Check back later for new opportunities!</p>
                            </div>
                            @endforelse
                        </div>
                        <div class="d-flex justify-content-center">
                            {{ $tasks->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection