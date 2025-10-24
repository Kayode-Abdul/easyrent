@extends('layout')

@section('title', 'Create New Role')

@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">
                            <i class="nc-icon nc-simple-add"></i> Create New Role
                        </h4>
                        <div>
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-info btn-sm">
                                <i class="nc-icon nc-bullet-list-67"></i> Back to Roles
                            </a>
                        </div>
                    </div>
                    <p class="card-category">Create a new role in the system</p>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8 offset-md-2">
                            <form action="{{ route('admin.roles.store') }}" method="POST">
                                @csrf
                                
                                <div class="form-group">
                                    <label for="name">Role Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           placeholder="Enter role name (e.g. content_editor)" value="{{ old('name') }}" required>
                                    <small class="form-text text-muted">
                                        This will be converted to a slug format (lowercase with underscores).
                                    </small>
                                    @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="display_name">Display Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="display_name" name="display_name" 
                                           placeholder="Enter display name (e.g. Content Editor)" value="{{ old('display_name') }}" required>
                                    <small class="form-text text-muted">
                                        This is the human-readable name that will be displayed in the interface.
                                    </small>
                                    @error('display_name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control" id="description" name="description" 
                                              rows="3" placeholder="Enter role description">{{ old('description') }}</textarea>
                                    @error('description')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                               {{ old('is_active') ? 'checked' : 'checked' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        If unchecked, this role will not be available for assignment.
                                    </small>
                                </div>
                                
                                <div class="form-group mt-4">
                                    <button type="submit" class="btn btn-primary">Create Role</button>
                                    <a href="{{ route('admin.roles.index') }}" class="btn btn-default">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
