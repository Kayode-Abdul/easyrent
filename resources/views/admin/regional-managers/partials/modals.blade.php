<!-- Add Regional Manager Modal -->
<div class="modal fade" id="addRegionalManagerModal" tabindex="-1" role="dialog" aria-labelledby="addRegionalManagerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRegionalManagerModalLabel">Add Regional Manager</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addRegionalManagerForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email_address">Email Address *</label>
                                <input type="email" class="form-control" id="email_address" name="email_address" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone_number">Phone Number</label>
                                <input type="text" class="form-control" id="phone_number" name="phone_number">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password">Password *</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password_confirmation">Confirm Password *</label>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="regions">Assign Regions</label>
                        <select class="form-control" id="regions" name="regions[]" multiple>
                            <option value="Lagos">Lagos</option>
                            <option value="Abuja">Abuja</option>
                            <option value="Port Harcourt">Port Harcourt</option>
                            <option value="Kano">Kano</option>
                            <option value="Ibadan">Ibadan</option>
                            <option value="Kaduna">Kaduna</option>
                            <option value="Jos">Jos</option>
                            <option value="Benin">Benin</option>
                            <option value="Enugu">Enugu</option>
                            <option value="Calabar">Calabar</option>
                        </select>
                        <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple regions</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Regional Manager</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Regional Manager Modal -->
<div class="modal fade" id="editRegionalManagerModal" tabindex="-1" role="dialog" aria-labelledby="editRegionalManagerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editRegionalManagerModalLabel">Edit Regional Manager</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editRegionalManagerForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_user_id" name="user_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_first_name">First Name *</label>
                                <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_last_name">Last Name *</label>
                                <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_email_address">Email Address *</label>
                                <input type="email" class="form-control" id="edit_email_address" name="email_address" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_phone_number">Phone Number</label>
                                <input type="text" class="form-control" id="edit_phone_number" name="phone_number">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_regions">Assign Regions</label>
                        <select class="form-control" id="edit_regions" name="regions[]" multiple>
                            <option value="Lagos">Lagos</option>
                            <option value="Abuja">Abuja</option>
                            <option value="Port Harcourt">Port Harcourt</option>
                            <option value="Kano">Kano</option>
                            <option value="Ibadan">Ibadan</option>
                            <option value="Kaduna">Kaduna</option>
                            <option value="Jos">Jos</option>
                            <option value="Benin">Benin</option>
                            <option value="Enugu">Enugu</option>
                            <option value="Calabar">Calabar</option>
                        </select>
                        <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple regions</small>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="change_password" name="change_password">
                            Change Password
                        </label>
                    </div>
                    <div id="password_fields" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_password">New Password</label>
                                    <input type="password" class="form-control" id="edit_password" name="password">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_password_confirmation">Confirm New Password</label>
                                    <input type="password" class="form-control" id="edit_password_confirmation" name="password_confirmation">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Regional Manager</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Regions Modal -->
<div class="modal fade" id="assignRegionsModal" tabindex="-1" role="dialog" aria-labelledby="assignRegionsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignRegionsModalLabel">Assign Regions</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="assignRegionsForm">
                @csrf
                <input type="hidden" id="assign_user_id" name="user_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Regional Manager: <span id="assign_manager_name"></span></label>
                    </div>
                    <div class="form-group">
                        <label for="assign_regions_select">Select Regions</label>
                        <select class="form-control" id="assign_regions_select" name="regions[]" multiple size="10">
                            <option value="Lagos">Lagos</option>
                            <option value="Abuja">Abuja</option>
                            <option value="Port Harcourt">Port Harcourt</option>
                            <option value="Kano">Kano</option>
                            <option value="Ibadan">Ibadan</option>
                            <option value="Kaduna">Kaduna</option>
                            <option value="Jos">Jos</option>
                            <option value="Benin">Benin</option>
                            <option value="Enugu">Enugu</option>
                            <option value="Calabar">Calabar</option>
                        </select>
                        <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple regions</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Regions</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Deletion</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to remove <strong id="delete_manager_name"></strong> as a Regional Manager?</p>
                <p class="text-danger"><small>This action cannot be undone. The user will lose Regional Manager privileges but their account will remain active.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Remove Regional Manager</button>
            </div>
        </div>
    </div>
</div>