<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/images/logo-small.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>@yield('title', 'EasyRent Dashboard')</title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Fonts and icons -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
    
    <!-- CSS Files -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/paper-dashboard.css') }}" rel="stylesheet" />
    
    <!-- Custom CSS -->
    <style>
        .commission-breakdown {
            font-size: 0.875rem;
        }
        .commission-breakdown .d-flex {
            margin-bottom: 2px;
        }
        .commission-breakdown hr {
            margin: 5px 0;
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
        <!-- Sidebar -->
        <div class="sidebar" data-color="white" data-active-color="danger">
            <div class="logo">
                <a href="{{ url('/dashboard') }}" class="simple-text logo-mini">
                    <div class="logo-image-small">
                        <img src="{{ asset('assets/images/logo-small.png') }}">
                    </div>
                </a>
                <a href="{{ url('/dashboard') }}" class="simple-text logo-normal">
                    EasyRent
                </a>
            </div>
            <div class="sidebar-wrapper">
                <ul class="nav">
                    <li class="{{ request()->is('dashboard') ? 'active' : '' }}">
                        <a href="{{ url('/dashboard') }}">
                            <i class="nc-icon nc-bank"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="{{ request()->is('dashboard/myproperty') ? 'active' : '' }}">
                        <a href="{{ url('/dashboard/myproperty') }}">
                            <i class="nc-icon nc-istanbul"></i>
                            <p>My Properties</p>
                        </a>
                    </li>
                    <li class="{{ request()->is('dashboard/commission-transparency') ? 'active' : '' }}">
                        <a href="{{ route('landlord.commission-transparency') }}">
                            <i class="nc-icon nc-chart-pie-36"></i>
                            <p>Commission Transparency</p>
                        </a>
                    </li>
                    <li class="{{ request()->is('dashboard/payments') ? 'active' : '' }}">
                        <a href="{{ url('/dashboard/payments') }}">
                            <i class="nc-icon nc-money-coins"></i>
                            <p>Payments</p>
                        </a>
                    </li>
                    <li class="{{ request()->is('dashboard/messages*') ? 'active' : '' }}">
                        <a href="{{ url('/dashboard/messages/inbox') }}">
                            <i class="nc-icon nc-email-85"></i>
                            <p>Messages</p>
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
                        <a class="navbar-brand" href="#pablo">@yield('title', 'Dashboard')</a>
                    </div>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navigation" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-bar navbar-kebab"></span>
                        <span class="navbar-toggler-bar navbar-kebab"></span>
                        <span class="navbar-toggler-bar navbar-kebab"></span>
                    </button>
                    <div class="collapse navbar-collapse justify-content-end" id="navigation">
                        <ul class="navbar-nav">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="nc-icon nc-single-02"></i>
                                    {{ auth()->user()->first_name ?? 'User' }}
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ url('/dashboard/user') }}">Profile</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
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
    
    <!-- DataTables (optional) -->
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
        
        // Commission transparency functions (shared across views)
        function viewCommissionDetails(paymentId) {
            $('#commissionDetailsModal').modal('show');
            
            $.ajax({
                url: `/dashboard/payment/${paymentId}/commission-details`,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        let content = `
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Payment Information</h6>
                                    <table class="table table-sm">
                                        <tr><td><strong>Amount:</strong></td><td>₦${parseFloat(response.payment.amount).toLocaleString()}</td></tr>
                                        <tr><td><strong>Property:</strong></td><td>${response.payment.property_address || 'N/A'}</td></tr>
                                        <tr><td><strong>Apartment:</strong></td><td>${response.payment.apartment_type || 'N/A'}</td></tr>
                                        <tr><td><strong>Tenant:</strong></td><td>${response.payment.tenant_name || 'N/A'}</td></tr>
                                        <tr><td><strong>Date:</strong></td><td>${response.payment.payment_date}</td></tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6>Commission Summary</h6>
                                    <table class="table table-sm">
                                        <tr><td><strong>Total Commission:</strong></td><td class="text-warning">₦${parseFloat(response.commission_breakdown.total_commission || 0).toLocaleString()}</td></tr>
                                        <tr><td><strong>Commission %:</strong></td><td>${parseFloat(response.commission_breakdown.commission_percentage || 0).toFixed(2)}%</td></tr>
                                        <tr><td><strong>Net Amount:</strong></td><td class="text-success">₦${parseFloat(response.commission_breakdown.net_amount || response.payment.amount).toLocaleString()}</td></tr>
                                    </table>
                                </div>
                            </div>
                        `;
                        
                        if (response.commission_breakdown.breakdown && response.commission_breakdown.breakdown.length > 0) {
                            content += `
                                <hr>
                                <h6>Commission Distribution</h6>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Tier</th>
                                                <th>Recipient</th>
                                                <th>Amount</th>
                                                <th>Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                            `;
                            
                            response.commission_breakdown.breakdown.forEach(function(item) {
                                content += `
                                    <tr>
                                        <td>${item.tier.replace('_', ' ').toUpperCase()}</td>
                                        <td>${item.recipient ? item.recipient.name : 'N/A'}</td>
                                        <td>₦${parseFloat(item.amount).toLocaleString()}</td>
                                        <td>${parseFloat(item.percentage).toFixed(2)}%</td>
                                    </tr>
                                `;
                            });
                            
                            content += `
                                        </tbody>
                                    </table>
                                </div>
                            `;
                        } else {
                            content += `
                                <hr>
                                <div class="alert alert-info">
                                    No commission breakdown available for this payment.
                                </div>
                            `;
                        }
                        
                        $('#commissionDetailsContent').html(content);
                    } else {
                        $('#commissionDetailsContent').html('<div class="alert alert-danger">Failed to load commission details.</div>');
                    }
                },
                error: function() {
                    $('#commissionDetailsContent').html('<div class="alert alert-danger">Error loading commission details.</div>');
                }
            });
        }
    </script>
    
    @yield('scripts')
</body>
</html>