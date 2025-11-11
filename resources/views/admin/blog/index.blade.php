@extends('layout')

@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Blog Management</h4>
                        <a href="{{ route('admin.blog.create') }}" class="btn btn-primary">
                            <i class="nc-icon nc-simple-add"></i> Create New Post
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table">
                            <thead class="text-primary">
                                <tr>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($posts as $post)
                                    <tr>
                                        <td>
                                            <strong>{{ $post->topic }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $post->topic_url }}</small>
                                        </td>
                                        <td>{{ $post->author }}</td>
                                        <td>{{ $post->date->format('M d, Y') }}</td>
                                        <td>
                                            @if($post->published && !$post->hide)
                                                <span class="badge badge-success">Published</span>
                                            @else
                                                <span class="badge badge-secondary">Draft</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <a href="/readmore/{{ $post->topic_url }}" target="_blank" class="btn btn-sm btn-info" title="View">
                                                <i class="nc-icon nc-zoom-split"></i>
                                            </a>
                                            <a href="{{ route('admin.blog.edit', $post->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="nc-icon nc-ruler-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.blog.destroy', $post->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this post?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="nc-icon nc-simple-remove"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No blog posts found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($posts->hasPages())
                        <div class="d-flex justify-content-center">
                            {{ $posts->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection