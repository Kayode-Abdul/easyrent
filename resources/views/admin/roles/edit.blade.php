@extends('layout')

@section('title', 'Edit Role')

@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">
                            <i class="nc-icon nc-ruler-pencil"></i> Edit Role: {{ $role->display_name }}
                        </h4>
                        <div>
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-info btn-sm">
                                <i class="nc-icon nc-bullet-list-67"></i> Back to Roles
                            </a>
                        </div>
                    </div>
                    <p class="card-category">Update role details</p>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8 offset-md-2">
                            <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="form-group">
                                    <label>Role Name</label>
                                    <input type="text" class="form-control" value="{{ $role->name }}" disabled>
                                    <small class="form-text text-muted">
                                        The role name cannot be changed after creation to maintain system integrity.
                                    </small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="display_name">Display Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="display_name" name="display_name" 
                                           value="{{ old('display_name', $role->display_name) }}" required>
                                    @error('display_name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control" id="description" name="description" 
                                              rows="3">{{ old('description', $role->description) }}</textarea>
                                    @error('description')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                               {{ old('is_active', $role->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        If unchecked, this role will not be available for assignment.
                                    </small>
                                </div>
                                
                                <div class="form-group mt-4">
                                    <button type="submit" class="btn btn-primary">Update Role</button>
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
