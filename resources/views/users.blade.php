<!-- Header area start -->
@include('header')
<!-- Header area end -->

<!-- cart-area start -->
<div class="content">
    <div class="container">
        <div class="form">
            <div class="cart-wrapper">
                <div class="row">
                    <div class="col-12">                        <!-- Admin information for role management -->
                        @if(auth()->check() && auth()->user()->admin == 1)
                        <div class="alert alert-info mb-4">
                            <h4>User & Role Management</h4>
                            <p>As an administrator, you can manage user roles in two ways:</p>
                            <ul>
                                <li>Click the <i class="fa fa-user-plus"></i> icon next to a user to directly access their role management</li>
                                <li>Go to <a href="{{ route('admin.roles.index') }}" class="alert-link">Role Management</a> to see all roles and manage users by role</li>
                            </ul>
                        </div>
                        @endif
                        
                        <!-- Search Form -->
                        <div class="mb-4">
                            <input type="text" id="searchInput" class="form-control" placeholder="Search users...">
                        </div>
                        <table class="table" id="usersTable">
                            <thead>
                                <tr>
                                    <th class="images images-b">Image</th>
                                    <th class="product-2">User Name</th>
                                    <th class="pr">Email</th>
                                    <th class="ptice">Role</th>
                                    <th class="remove remove-b">Action</th>
                                </tr>
                            </thead>
                            <tbody>                    @php
                  $roles = ['Landlord','Tenant','Artisan','Property Manager', 'Marketer', 'Regional Manager'];
                @endphp
                @if (auth()->check())
                    @php $logged_in = 1; @endphp
                @else
                    @php $logged_in = 0; @endphp
                @endif
                @if(!empty($users))
                @foreach(array_reverse($users->toArray()) as $user)
                <tr data-user-id="{{ $user['user_id'] }}">
                <td class="images">
                    <div class="avatar">
                    <img class="img-circle img-no-padding border-gray" src="{{ $user['photo'] ? asset($user['photo']) : asset('assets/images/default-avatar.png') }}" alt="...">
                    <span class="username">{{ $user['username'] }}</span>
                    </div>
                </td>
                <td class="product">{{ $user['first_name'].' '.$user['last_name'] }} </td>
                <td class="stock">
                {{ $user['email'] }}
                </td>                <td class="stock">
                {{ isset($roles[($user['role'])-1]) ? $roles[($user['role'])-1] : 'Unknown Role' }}
                </td>                <td class="action">
                    <a href="{{ route('users.profile', ['id' => $user['user_id']]) }}" class="view-user"><i class="fa fa-eye" aria-hidden="true" title="View details"></i></a>
                    
                    @if(auth()->check() && auth()->user()->admin == 1)
                        <!-- Admin specific actions for role management -->
                        <a href="{{ route('users.profile', ['id' => $user['user_id']]) }}#role-management" class="btn btn-primary btn-sm ml-2" title="Manage Role">
                            <i class="fa fa-user-plus" aria-hidden="true"></i>
                        </a>
                    @endif
                    
                    @if(auth()->check() && auth()->user()->user_id !== $user['user_id'])
                        @php
                            $authUser = auth()->user();
                            $targetUser = (object) $user; // Convert array to object for helper methods
                        @endphp
                        @if(
                            // Landlord/Agent viewing their tenant
                            ($authUser->isLandlord() && $authUser->isLandlordOf($targetUser)) ||
                            ($authUser->isAgent() && $authUser->isLandlordOf($targetUser)) ||
                            // Tenant viewing their landlord/agent
                            ($authUser->isTenant() && (
                                $targetUser->isLandlord && $authUser->isTenantOf($targetUser)
                                || $targetUser->isAgent && $authUser->isTenantOf($targetUser)
                            ))
                        )
                            <a href="{{ url('/messages/compose?to=' . $user['user_id']) }}" class="btn btn-success btn-sm ml-2" title="Message">
                                <i class="fa fa-envelope"></i>
                            </a>
                        @endif
                    @endif
                </td>
                </tr>
                @endforeach
                @endif
 
                    </tbody>
                        </table>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            <nav aria-label="Page navigation">
                                <ul class="pagination">
                                    <?php
                                    $total_pages = ceil(count($users) / 10); // Show 10 items per page
                                    for($i = 1; $i <= $total_pages; $i++) {
                                        echo "<li class='page-item'><a class='page-link' href='#' data-page='$i'>$i</a></li>";
                                    }
                                    ?>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add this JavaScript before the closing body tag -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemsPerPage = 10;
    const table = document.getElementById('usersTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    const searchInput = document.getElementById('searchInput');
    
    // Search functionality
    searchInput.addEventListener('keyup', function() {
        const searchText = this.value.toLowerCase();
        Array.from(rows).forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchText) ? '' : 'none';
        });
    });
    
    // Pagination functionality
    function showPage(pageNum) {
        Array.from(rows).forEach((row, index) => {
            const start = (pageNum - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            row.style.display = (index >= start && index < end) ? '' : 'none';
        });
    }
    
    // Add click handlers to pagination links
    document.querySelectorAll('.pagination .page-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const pageNum = parseInt(this.getAttribute('data-page'));
            showPage(pageNum);
            
            // Update active state
            document.querySelectorAll('.page-item').forEach(item => item.classList.remove('active'));
            this.parentElement.classList.add('active');
        });
    });
      // We don't need this event handler anymore since we've updated our links with proper hrefs
    // The eye icon now links directly to the user profile page
    // Other buttons like message already have their URLs set
    
    // Show first page by default
    showPage(1);
    document.querySelector('.pagination .page-link').parentElement.classList.add('active');
});
</script>

<!-- Footer area start -->
@include('footer')
<!-- Footer area end -->
    <!-- <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
