<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Easyrent- All Landlords Property Manager</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta charset="utf-8"> 
    <link rel="icon" type="image/png" href="/favicon.png">
    @php $currentSegment = request()->segment(1); $isDashboard = in_array($currentSegment, ['dashboard','admin', 'proforma']); @endphp
    @if(!$isDashboard)
    <!-- Add CSRF Token meta tag -->
    <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:200,300,400,600,700,800,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/open-iconic-bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/animate.css">
    <link rel="stylesheet" href="/assets/css/owl.carousel.min.css">
    <link rel="stylesheet" href="/assets/css/owl.theme.default.min.css">
    <link rel="stylesheet" href="/assets/css/magnific-popup.css">
    <link rel="stylesheet" href="/assets/css/aos.css">
    <link rel="stylesheet" href="/assets/css/ionicons.min.css">
<!--     
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"></script>
    <script src="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/js/bootstrap-datepicker.min.js"></script>
    <link id="bs-css" href="https://netdna.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet">
    <link id="bsdp-css" href="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/css/bootstrap-datepicker3.min.css" rel="stylesheet"> -->

    <!--daterange --> 
    <link id="bs-css" href="https://netdna.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet">
    <link id="bsdp-css" href="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/css/bootstrap-datepicker3.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/bootstrap-datepicker.css">
    <link rel="stylesheet" href="/assets/css/jquery.timepicker.css">
    <link rel="stylesheet" href="/assets/css/flaticon.css">
    <link rel="stylesheet" href="/assets/css/icomoon.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/custom-fixes.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @else
    <meta charset="utf-8" />
    <link rel="apple-touch-icon" sizes="76x76" href="/assets/img/apple-icon.png">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
    <!-- Add CSRF Token meta tag -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- Add jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Add Bootstrap 4 JS and Popper.js for modal support -->
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
// Global CSRF setup for all jQuery AJAX requests
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
</script>

    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
    <!-- CSS Files -->
    <link href="/assets/css/bootstrap/bootstrap.min.css" rel="stylesheet" />
    <link href="/assets/css/bootstrap/paper-dashboard.css?v=2.0.1" rel="stylesheet" />
    <!-- CSS Just for demo purpose, don't include it in your project --> 
    <link rel="stylesheet" href="/assets/css/custom-fixes.css">

    {{-- Moved jQuery/Moment/Daterangepicker to footer for correct load order --}}
 
    @endif
    @yield('styles')
   @stack('styles')
  </head>
  <body>
    @if(!$isDashboard)
      <nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
        <div class="container">
          <a class="navbar-brand" href="/">
            <img src="/assets/images/logo-small.png">
          </a>
          <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="oi oi-menu"></span> Menu
          </button>
          <div class="collapse navbar-collapse" id="ftco-nav">
            <ul class="navbar-nav ml-auto">
              <li class="nav-item {{ $currentSegment === '' ? 'active' : '' }}">
                <a href="/" class="nav-link">Home</a>
              </li>
              <li class="nav-item {{ $currentSegment === 'about' ? 'active' : '' }}">
                <a href="/about" class="nav-link">About</a>
              </li>
              <li class="nav-item {{ $currentSegment === 'services' ? 'active' : '' }}">
                <a href="/services" class="nav-link">Services</a>
              </li>
              @if(auth()->check())
                <li class="nav-item {{ $currentSegment === 'dashboard' ? 'active' : '' }}">
                  <a href="/dashboard" class="nav-link">Dashboard</a>
                </li>
               
                <li class="nav-item">
                  <a href="/logout" class="nav-link">Logout</a>
                </li>
              @else
                <li class="nav-item {{ $currentSegment === 'register' ? 'active' : '' }}">
                  <a href="/register" class="nav-link">Signup</a>
                </li>
                <li class="nav-item {{ $currentSegment === 'login' ? 'active' : '' }}">
                  <a href="/login" class="nav-link">Login</a>
                </li>
              @endif
              <li class="nav-item {{ $currentSegment === 'contact' ? 'active' : '' }}">
                <a href="/contact" class="nav-link">Contact</a>
              </li>
            </ul>
          </div>
        </div>
      </nav>
      <!-- END nav -->
    @else
    <div class="wrapper">
        <div class="sidebar" data-color="white" data-active-color="danger">
            <div class="logo">
                <a href="/" class="simple-text logo-mini">
                    <div class="logo-image-small">
                    </div>
                </a>
                <a href="/" class="simple-text logo-normal">
                        <img src="/assets/images/logo-small.png">
                   
                </a>
            </div>
            <div class="sidebar-wrapper">
                <ul class="nav">
                    <li class="{{ request()->is('dashboard') ? 'active' : '' }}">
                        <a href="/dashboard">
                            <i class="nc-icon nc-bank"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="{{ request()->is('dashboard/user') ? 'active' : '' }}">
                        <a href="/dashboard/user">
                            <i class="nc-icon nc-single-02"></i>
                            <p>User Profile</p>
                        </a>
                    </li>
                    <li class="{{ request()->is('dashboard/myproperty') ? 'active' : '' }}">
                        <a href="/dashboard/myproperty">
                            <i class="nc-icon nc-tile-56"></i>
                            <p>My Property(s)</p>
                        </a>
                    </li>
                    <li class="{{ request()->is('dashboard/billing*') ? 'active' : '' }}">
                        <a href="/dashboard/billing">
                            <i class="nc-icon nc-credit-card"></i>
                            <p>Billing</p>
                        </a>
                    </li>
                    <!-- Messages Dropdown -->
                    <li class="nav-item dropdown {{ request()->is('dashboard/messages*') ? 'active' : '' }}">
                        <a href="#" class="nav-link dropdown-toggle" id="messagesDropdown" data-toggle="collapse" data-target="#messagesMenu" aria-expanded="{{ request()->is('dashboard/messages*') ? 'true' : 'false' }}" aria-controls="messagesMenu">
                            <i class="nc-icon nc-email-85"></i>
                            <p>Messages
                                @php
                                    $unreadCount = Auth::user()->receivedMessages()->where('is_read', false)->count();
                                @endphp
                                @if($unreadCount > 0)
                                    <span class="badge badge-danger ml-1">{{ $unreadCount }}</span>
                                @endif
                            </p>
                        </a>
                        <div class="collapse {{ request()->is('dashboard/messages*') ? 'show' : '' }}" id="messagesMenu">
                            <ul class="nav flex-column ml-3">
                                <li class="nav-item {{ request()->is('dashboard/messages/inbox') ? 'active' : '' }}">
                                    <a class="nav-link" href="/dashboard/messages/inbox">Inbox
                                        @if($unreadCount > 0)
                                            <span class="badge badge-danger ml-1">{{ $unreadCount }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li class="nav-item {{ request()->is('dashboard/messages/sent') ? 'active' : '' }}">
                                    <a class="nav-link" href="/dashboard/messages/sent">Sent</a>
                                </li>
                                <li class="nav-item {{ request()->is('dashboard/messages/compose') ? 'active' : '' }}">
                                    <a class="nav-link" href="/dashboard/messages/compose">Compose</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @if(auth()->check() && Auth::user()->admin)
                    <li class="{{ request()->is('dashboard/user/payments*') ? 'active' : '' }}">
                        <a href="{{ route('payments.index') }}">
                            <i class="nc-icon nc-money-coins"></i>
                            <p>Payments</p>
                        </a>
                    </li>
                    <li class="{{ request()->is('dashboard/properties') ? 'active' : '' }}">
                        <a href="/dashboard/properties">
                            <i class="nc-icon nc-diamond"></i>
                            <p>Properties</p>
                        </a>
                    </li>
                    <li class="{{ request()->is('dashboard/users') ? 'active' : '' }}">
                        <a href="/dashboard/users">
                            <i class="nc-icon nc-pin-3"></i>
                            <p>Users</p>
                        </a>
                    </li>
                    <li class="{{ request()->is('admin/dashboard/roles*') ? 'active' : '' }}">
                        <a href="{{ route('admin.roles.index') }}">
                            <i class="nc-icon nc-key-25"></i>
                            <p>Role Management</p>
                        </a>
                    </li>
                    <li class="{{ request()->is('admin/dashboard/assign-role*') ? 'active' : '' }}">
                        <a href="{{ route('admin.roles.assign') }}">
                            <i class="nc-icon nc-single-02"></i>
                            <p>Assign User Roles</p>
                        </a>
                    </li>
                    <li class="{{ request()->is('admin/regional-managers*') ? 'active' : '' }}">
                        <a href="{{ route('admin.regional-managers.index') }}">
                            <i class="nc-icon nc-world-2"></i>
                            <p>Regional Managers</p>
                        </a>
                    </li>
                    <li class="{{ request()->is('admin/commission-rates*') ? 'active' : '' }}">
                        <a href="{{ route('admin.commission-rates.index') }}">
                            <i class="nc-icon nc-money-coins"></i>
                            <p>Commission Rates (Legacy)</p>
                        </a>
                    </li>
                    <li class="{{ request()->is('admin/commission-management*') ? 'active' : '' }}">
                        <a href="{{ route('admin.commission-management.regional-manager') }}">
                            <i class="nc-icon nc-settings-gear-65"></i>
                            <p>Commission Management</p>
                        </a>
                    </li>
                    @endif

                    @php
                        $hasRegionalManagerRole = false;
                        if (auth()->check()) {
                            $regionalManagerRoleId = DB::table('roles')->where('name', 'regional_manager')->value('id');
                            if (Auth::user()->role == $regionalManagerRoleId) $hasRegionalManagerRole = true;
                            if (session('selected_role') == 'regional_manager') $hasRegionalManagerRole = true;
                            if (isset($primaryRole) && $primaryRole == 'regional_manager') $hasRegionalManagerRole = true;
                            try {
                                if (method_exists(Auth::user(), 'roles') && Auth::user()->roles()->where('name', 'regional_manager')->exists()) {
                                    $hasRegionalManagerRole = true;
                                }
                            } catch (\Exception $e) {}
                        }
                    @endphp
                    
                    @if(auth()->check() && $hasRegionalManagerRole)
                    <!-- Regional Manager Navigation -->
                    <li class="{{ request()->is('dashboard/regional') ? 'active' : '' }}">
                        <a href="{{ route('regional.dashboard') }}">
                            <i class="nc-icon nc-chart-pie-36"></i>
                            <p>Regional Dashboard</p>
                        </a>
                    </li>
                    <li class="{{ request()->is('regional/properties*') ? 'active' : '' }}">
                        <a href="{{ route('regional.properties') }}">
                            <i class="nc-icon nc-istanbul"></i>
                            <p>Regional Properties</p>
                        </a>
                    </li>
                    <li class="{{ request()->is('regional/marketers*') ? 'active' : '' }}">
                        <a href="{{ route('regional.marketers') }}">
                            <i class="nc-icon nc-single-02"></i>
                            <p>Marketers</p>
                        </a>
                    </li>
                    <li class="{{ request()->is('regional/analytics*') ? 'active' : '' }}">
                        <a href="{{ route('regional.analytics') }}">
                            <i class="nc-icon nc-chart-bar-32"></i>
                            <p>Analytics</p>
                        </a>
                    </li>
                    <li class="{{ request()->is('regional/pending-approvals*') ? 'active' : '' }}">
                        <a href="{{ route('regional.pending_approvals') }}">
                            <i class="nc-icon nc-tag-content"></i>
                            <p>Pending Approvals</p>
                        </a>
                    </li>
                    @endif
                    
                    <li class="">
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="nc-icon nc-spaceship"></i>
                            <p>Log Out</p>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="main-panel">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-absolute fixed-top navbar-transparent">
                <div class="container-fluid">
                    <div class="navbar-wrapper">
                        <div class="navbar-toggle">
                            <button type="button" class="navbar-toggler">
                                <span class="navbar-toggler-bar bar1"></span>
                                <span class="navbar-toggler-bar bar2"></span>
                                <span class="navbar-toggler-bar bar3"></span>
                            </button>
                        </div>
                        <a class="navbar-brand" href="javascript:;">User Dashboard</a>
                        @if($hasRegionalManagerRole)
                            <a href="{{ route('regional.dashboard') }}" class="btn btn-sm btn-info ml-2">Regional Manager Dashboard</a>
                        @endif
                    </div>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navigation" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-bar navbar-kebab"></span>
                        <span class="navbar-toggler-bar navbar-kebab"></span>
                        <span class="navbar-toggler-bar navbar-kebab"></span>
                    </button>
                    <div class="collapse navbar-collapse justify-content-end" id="navigation">
                        @if(auth()->check())
                        <div class="mr-3 role-switcher-container">
                            <form action="{{ route('switch.role') }}" method="POST" class="role-switcher-form">
                                @csrf
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="nc-icon nc-settings-gear-65"></i> Role:
                                        </span>
                                    </div>
                                    <select name="role" class="form-control role-select">
                                        @php
                                            $availableRoles = [];
                                            
                                            // Add role from the user's role attribute using dynamic lookup
                                            $roleMap = [
                                                'admin' => DB::table('roles')->where('name', 'admin')->value('id'),
                                                'landlord' => DB::table('roles')->where('name', 'landlord')->value('id'),
                                                'tenant' => DB::table('roles')->where('name', 'tenant')->value('id'),
                                                'property_manager' => DB::table('roles')->where('name', 'property_manager')->value('id'),
                                                'marketer' => DB::table('roles')->where('name', 'marketer')->value('id'),
                                                'regional_manager' => DB::table('roles')->where('name', 'regional_manager')->value('id'),
                                            ];
                                            
                                            foreach($roleMap as $roleName => $roleId) {
                                                if (Auth::user()->role == $roleId) {
                                                    $availableRoles[] = $roleName;
                                                }
                                            }
                                            
                                            // Add admin if admin column is true
                                            if (Auth::user()->admin == 1 && !in_array('admin', $availableRoles)) {
                                                $availableRoles[] = 'admin';
                                            }
                                            
                                            // Add roles from $userRoles variable if it exists
                                            if (isset($userRoles) && is_array($userRoles)) {
                                                $availableRoles = array_unique(array_merge($availableRoles, $userRoles));
                                            }

                                            // Set current role
                                            $currentRole = $primaryRole ?? 'default';
                                        @endphp

                                        @foreach($availableRoles as $role)
                                            <option value="{{ $role }}" {{ $currentRole == $role ? 'selected' : '' }}>
                                                {{ ucfirst(str_replace('_', ' ', $role)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-sm btn-primary">Switch</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <style>
                            .role-switcher-container {
                                background-color: rgba(255, 255, 255, 0.1);
                                padding: 5px;
                                border-radius: 4px;
                            }
                            .role-select {
                                font-weight: bold;
                            }
                            .input-group-text {
                                background-color: #f5f5f5;
                                color: #555;
                                font-weight: 500;
                            }
                            .debug-info {
                                font-size: 10px;
                                color: #999;
                                margin-top: 2px;
                            }
                        </style>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const roleSelect = document.querySelector('.role-select');
                                if (roleSelect) {
                                    roleSelect.addEventListener('change', function() {
                                        this.closest('form').submit();
                                    });
                                }
                            });
                        </script>
                        @if(config('app.debug'))
                        <div class="debug-info">
                            User ID: {{ Auth::id() }} | 
                            Role: {{ Auth::user()->role }} | 
                            Admin: {{ Auth::user()->admin ? 'Yes' : 'No' }}
                        </div>
                        @endif
                        @endif
                        <!-- <form>
                            <div class="input-group no-border">
                                <input type="text" value="" class="form-control" placeholder="Search...">
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <i class="nc-icon nc-zoom-split"></i>
                                    </div>
                                </div>
                            </div>
                        </form> -->
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link btn-magnify" href="javascript:;">
                                    <i class="nc-icon nc-layout-11"></i>
                                    <p>
                                        <span class="d-lg-none d-md-block">Stats</span>
                                    </p>
                                </a>
                            </li>
                            <li class="nav-item btn-rotate dropdown position-relative">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" onclick="markNotificationsSeen()">
                                    <i class="nc-icon nc-bell-55"></i>
                                    <span id="notification-badge" class="badge badge-pill badge-danger position-absolute" style="top:8px;right:2px;display:none;z-index:10;font-size:0.7rem;">0</span>
                                    <p>
                                        <span class="d-lg-none d-md-block">Notifications</span>
                                    </p>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                                    <a class="dropdown-item" href="#">No new notifications</a>
                                </div>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link btn-rotate" href="javascript:;">
                                    <i class="nc-icon nc-settings-gear-65"></i>
                                    <p>
                                        <span class="d-lg-none d-md-block">Account</span>
                                    </p>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
            <!-- End Navbar -->
    @endif