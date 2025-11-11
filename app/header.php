<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Easyrent- All Landlords Property Manager</title>
    
    <?php 
    $currentSegment = request()->segment(1);
    if($currentSegment !== 'dashboard'){
    ?>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:200,300,400,600,700,800,900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/assets/css/open-iconic-bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/animate.css">
    
    <link rel="stylesheet" href="/assets/css/owl.carousel.min.css">
    <link rel="stylesheet" href="/assets/css/owl.theme.default.min.css">
    <link rel="stylesheet" href="/assets/css/magnific-popup.css">

    <link rel="stylesheet" href="/assets/css/aos.css">

    <link rel="stylesheet" href="/assets/css/ionicons.min.css">
   
    <!--daterange -->
    <link rel="stylesheet" href="/assets/css/bootstrap-datepicker.css">
    <link rel="stylesheet" href="/assets/css/jquery.timepicker.css">

    
    <link rel="stylesheet" href="/assets/css/flaticon.css">
    <link rel="stylesheet" href="/assets/css/icomoon.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/custom-fixes.css">
    <link rel="stylesheet" href="/assets/css/custom-fixes.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
    <?php 
    } else {
    ?>

    <meta charset="utf-8" />
    <link rel="apple-touch-icon" sizes="76x76" href="/assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="/assets/img/favicon.png">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
    <!-- CSS Files -->
    <link href="/assets/css/bootstrap/bootstrap.min.css" rel="stylesheet" />
    <link href="/assets/css/bootstrap/paper-dashboard.css?v=2.0.1" rel="stylesheet" />
    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link href="/assets/demo/demo.css" rel="stylesheet" />
    <link rel="stylesheet" href="/assets/css/custom-fixes.css"> 
    <?php 
    }
    ?>

  </head>
  <body>
    
  <?php  
    if($currentSegment != 'dashboard'){
  ?>
	  <nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
	    <div class="container">
	      <a class="navbar-brand" href="/">Easy Rent</a>
	      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
	        <span class="oi oi-menu"></span> Menu
	      </button>

	      <div class="collapse navbar-collapse" id="ftco-nav">
	        <ul class="navbar-nav ml-auto">
	          <li class="nav-item <?= $currentSegment === '' ? 'active' : '' ?>">
                <a href="/" class="nav-link">Home</a>
              </li>
	          <li class="nav-item <?= $currentSegment === 'about' ? 'active' : '' ?>">
                <a href="/about" class="nav-link">About</a>
              </li>
	          <li class="nav-item <?= $currentSegment === 'services' ? 'active' : '' ?>">
                <a href="/services" class="nav-link">Services</a>
              </li>
            <?php if(session('loggedIn')){ ?>
	          <li class="nav-item <?= $currentSegment === 'dashboard' ? 'active' : '' ?>">
                <a href="/dashboard" class="nav-link">Dashboard</a>
              </li>
              <li class="nav-item">
                <a href="/logout" class="nav-link">Logout</a>
              </li>
            <?php } else { ?>
	          <li class="nav-item <?= $currentSegment === 'register' ? 'active' : '' ?>">
                <a href="/register" class="nav-link">Signup</a>
              </li>
	          <li class="nav-item <?= $currentSegment === 'login' ? 'active' : '' ?>">
                <a href="/login" class="nav-link">Login</a>
              </li>
	          <?php } ?>
            <li class="nav-item <?= $currentSegment === 'contact' ? 'active' : '' ?>">
                <a href="/contact" class="nav-link">Contact</a>
            </li>
	        </ul>
	      </div>
	    </div>
	  </nav>
    <!-- END nav -->

    <?php 
    } else {
    ?>
    <div class="wrapper">
        <div class="sidebar" data-color="white" data-active-color="danger">
            <div class="logo">
                <a href="/" class="simple-text logo-mini">
                    <div class="logo-image-small">
                        <img src="/assets/img/logo-small.png">
                    </div>
                </a>
                <a href="/" class="simple-text logo-normal">
                    Easy Rentiiii
                </a>
            </div>
            <div class="sidebar-wrapper">
                <ul class="nav">
                    
                    <li class="<?= request()->is('dashboard') ? 'active' : '' ?>">
                        <a href="/dashboard">
                            <i class="nc-icon nc-bank"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
            <?php
            if(session('isAdmin')){
                ?>
                    <li class="<?= request()->is('dashboard/properties') ? 'active' : '' ?>">
                        <a href="/dashboard/properties">
                            <i class="nc-icon nc-diamond"></i>
                            <p>Properties</p>
                        </a>
                    </li>
                    <li class="<?= request()->is('dashboard/users') ? 'active' : '' ?>">
                        <a href="/dashboard/users">
                            <i class="nc-icon nc-pin-3"></i>
                            <p>Users</p>
                        </a>
                    </li>
            <?php }?>
                    <li class="<?= request()->is('dashboard/user') ? 'active' : '' ?>">
                        <a href="/dashboard/user">
                            <i class="nc-icon nc-single-02"></i>
                            <p>User Profile</p>
                        </a>
                    </li>
                    <li class="<?= request()->is('dashboard/myproperty') ? 'active' : '' ?>">
                        <a href="/dashboard/myproperty">
                            <i class="nc-icon nc-tile-56"></i>
                            <p>My Property(s)</p>
                        </a>
                    </li>
                    <li class="<?= request()->is('dashboard/notifications') ? 'active' : '' ?>">
                        <a href="/dashboard/notifications">
                            <i class="nc-icon nc-bell-55"></i>
                            <p>Notifications</p>
                        </a>
                    </li>
                    <li class="active-pro">
                        <a href="/logout">
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
                        <a class="navbar-brand" href="/dashboard">User Dashboard</a>
                    </div>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navigation" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-bar navbar-kebab"></span>
                        <span class="navbar-toggler-bar navbar-kebab"></span>
                        <span class="navbar-toggler-bar navbar-kebab"></span>
                    </button>
                    <div class="collapse navbar-collapse justify-content-end" id="navigation">
                        <form>
                            <div class="input-group no-border">
                                <input type="text" value="" class="form-control" placeholder="Search...">
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <i class="nc-icon nc-zoom-split"></i>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link btn-magnify" href="javascript:;">
                                    <i class="nc-icon nc-layout-11"></i>
                                    <p>
                                        <span class="d-lg-none d-md-block">Stats</span>
                                    </p>
                                </a>
                            </li>
                            <li class="nav-item btn-rotate dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="nc-icon nc-bell-55"></i>
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
    <?php 
    }
    ?>