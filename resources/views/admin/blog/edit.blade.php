@extends('layout')

@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Edit Blog Post</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.blog.update', $post->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="topic">Title *</label>
                                    <input type="text" class="form-control @error('topic') is-invalid @enderror" 
                                           id="topic" name="topic" value="{{ old('topic', $post->topic) }}" required>
                                    @error('topic')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="content">Content *</label>
                                    <textarea class="form-control @error('content') is-invalid @enderror" 
                                              id="content" name="content" rows="15" required>{{ old('content', $post->content) }}</textarea>
                                    @error('content')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="author">Author *</label>
                                    <input type="text" class="form-control @error('author') is-invalid @enderror" 
                                           id="author" name="author" value="{{ old('author', $post->author) }}" required>
                                    @error('author')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="cover_photo">Cover Photo URL</label>
                                    <input type="url" class="form-control @error('cover_photo') is-invalid @enderror" 
                                           id="cover_photo" name="cover_photo" value="{{ old('cover_photo', $post->cover_photo) }}" 
                                           placeholder="https://example.com/image.jpg">
                                    @error('cover_photo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="published" 
                                               name="published" value="1" {{ old('published', $post->published) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="published">
                                            Published
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <small class="text-muted">
                                        <strong>URL:</strong> {{ $post->topic_url }}<br>
                                        <strong>Created:</strong> {{ $post->created_at->format('M d, Y H:i') }}<br>
                                        <strong>Updated:</strong> {{ $post->updated_at->format('M d, Y H:i') }}
                                    </small>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="nc-icon nc-check-2"></i> Update Post
                                    </button>
                                    <a href="{{ route('admin.blog.index') }}" class="btn btn-secondary btn-block">
                                        <i class="nc-icon nc-minimal-left"></i> Cancel
                                    </a>
                                    <a href="/readmore/{{ $post->topic_url }}" target="_blank" class="btn btn-info btn-block">
                                        <i class="nc-icon nc-zoom-split"></i> Preview
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection