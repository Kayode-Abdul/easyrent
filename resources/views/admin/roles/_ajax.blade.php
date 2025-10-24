@if(isset($users) && count($users))
<div class="table-responsive">
  <table class="table table-striped">
    <thead>
      <tr>
        <th>User ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Primary Role</th>
      </tr>
    </thead>
    <tbody>
      @foreach($users as $u)
      <tr>
        <td>{{ $u->user_id }}</td>
        <td>{{ $u->first_name }} {{ $u->last_name }}</td>
        <td>{{ $u->email }}</td>
        <td>{{ is_numeric($u->role) ? (['','admin','landlord','tenant','property_manager','marketer','regional_manager'][$u->role] ?? $u->role) : $u->role }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
{{ method_exists($users,'links') ? $users->links() : '' }}
@else
  <p class="text-muted mb-0">No users found for this role.</p>
@endif
