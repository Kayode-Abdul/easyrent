<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/images/logo-small.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>@yield('title', 'EasyRent Admin Dashboard')</title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Fonts and icons -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
    
    <!-- CSS Files -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/paper-dashboard.css') }}" rel="stylesheet" />
    
    <!-- Custom Admin CSS -->
    <style>
        .sidebar[data-color="admin"] {
            background: linear-gradient(0deg, #1f8ef1, #1171ef);
        }
        .sidebar[data-color="admin"] .nav li.active > a {
            background-color: rgba(255, 255, 255, 0.2);
            color: #FFFFFF;
        }
        .admin-badge {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
        }
        .card-stats .numbers {
            text-align: right;
        }
        .card-stats .icon-big {
            text-align: center;
        }
        .table th {
            border-top: none;
        }
        .modal-xl {
            max-width: 90%;
        }
        @media (max-width: 768px) {
            .modal-xl {
                max-width: 95%;
            }
        }
    </style>
    
    @yield('styles')
</head>

<body class="">
    <div class="wrapper">
        <!-- Admin Sidebar -->
        <div class="sidebar" data-color="admin" data-active-color="danger">
            <div class="logo">
                <a href="{{ url('/admin/dashboard') }}" class="simple-text logo-mini">
                    <div class="logo-image-small">
                        <img src="{{ asset('assets/images/logo-small.png') }}">
                    </div>
                </a>
                <a href="{{ url('/admin/dashboard') }}" class="simple-text logo-normal">
                    EasyRent <span class="admin-badge">ADMIN</span>
                </a>
            </div>
            <div class="sidebar-wrapper">
                <ul class="nav">
                    <li class="{{ request()->is('admin/dashboard') ? 'active' : '' }}">
                        <a href="{{ url('/admin/dashboard') }}">
                            <i class="nc-icon nc-bank"></i>
                            <p>Admin Dashboard</p>
                        </a>
                    </li>
                    <li class="{{ request()->is('admin/properties*') ? 'active' : '' }}">
                        <a href="{{ url('/admin/properties') }}">
                            <i class="nc-icon nc-istanbul"></i>
                            <p>Properties</p>
                        </a>
                    </li>
                    <li class="{{ request()->is('admin/users*') ? 'active' : '' }}">
                        <a href="{{ url('/admin/users') }}">
                            <i class="nc-icon nc-single-02"></i>
                            <p>Users</p>
                        </a>
                    </li>
                    <li class="{{ request()->is('admin/payments*') ? 'active' : '' }}">
                        <a href="{{ url('/admin/payments') }}">
                            <i class="nc-icon nc-money-coins"></i>
                            <p>Payments</p>
                        </a>
                    </li>
                    <li class="{{ request()->is('admin/regional-managers*') ? 'active' : '' }}">
                        <a href="{{ url('/admin/regional-managers') }}">
                            <i class="nc-icon nc-badge"></i>
                            <p>Regional Managers</p>
                        </a>
                    </li>
                    <li class="{{ request()->is('admin/commission*') ? 'active' : '' }}">
                        <a href="{{ url('/admin/commission-rates') }}">
                            <i class="nc-icon nc-chart-pie-36"></i>
                            <p>Commission Rates</p>
                        </a>
                    </li>
                    <li class="{{ request()->is('admin/reports*') ? 'active' : '' }}">
                        <a href="{{ url('/admin/reports') }}">
                            <i class="nc-icon nc-chart-bar-32"></i>
                            <p>Reports</p>
                        </a>
                    </li>
                    <li class="{{ request()->is('admin/settings*') ? 'active' : '' }}">
                        <a href="{{ url('/admin/settings') }}">
                            <i class="nc-icon nc-settings"></i>
                            <p>Settings</p>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Main Panel -->
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
                        <a class="navbar-brand" href="#pablo">@yield('title', 'Admin Dashboard')</a>
                    </div>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navigation" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-bar navbar-kebab"></span>
                        <span class="navbar-toggler-bar navbar-kebab"></span>
                        <span class="navbar-toggler-bar navbar-kebab"></span>
                    </button>
                    <div class="collapse navbar-collapse justify-content-end" id="navigation">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link" href="{{ url('/dashboard') }}">
                                    <i class="nc-icon nc-minimal-left"></i>
                                    Back to User Dashboard
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="nc-icon nc-single-02"></i>
                                    {{ auth()->user()->first_name ?? 'Admin' }}
                                    <span class="admin-badge">ADMIN</span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ url('/dashboard/user') }}">Profile</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="#"
                                       onclick="handleLogout('logout-form')">
                                        Logout
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
            
            <!-- Content -->
            @yield('content')
            
            <!-- Footer -->
            <footer class="footer footer-black footer-white">
                <div class="container-fluid">
                    <div class="row">
                        <nav class="footer-nav">
                            <ul>
                                <li><a href="{{ url('/') }}">EasyRent</a></li>
                                <li><a href="{{ url('/about') }}">About</a></li>
                                <li><a href="{{ url('/contact') }}">Contact</a></li>
                            </ul>
                        </nav>
                        <div class="credits ml-auto">
                            <span class="copyright">
                                © {{ date('Y') }}, made with <i class="fa fa-heart heart"></i> by EasyRent Team
                            </span>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    
    <!-- Core JS Files -->
    <script src="{{ asset('assets/js/core/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/perfect-scrollbar.jquery.min.js') }}"></script>
    
    <!-- Paper Dashboard CORE plugins -->
    <script src="{{ asset('assets/js/paper-dashboard.min.js') }}"></script>
    
    <!-- Global logout handler -->
    <script src="{{ asset('assets/js/logout-handler.js') }}"></script>
    
    <!-- Chrome Dark Mode System -->
    <link rel="stylesheet" href="{{ asset('assets/css/chrome-dark-mode.css') }}">
    <script src="{{ asset('assets/js/chrome-dark-mode.js') }}"></script>
    
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
    
    <!-- Global CSRF setup for AJAX -->
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    
    @yield('scripts')
    @stack('scripts')
</body>
</html>