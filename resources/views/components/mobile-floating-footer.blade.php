@php
    $currentUser = auth()->user();
    $currentSegment = request()->segment(1);
    $isDashboard = in_array($currentSegment, ['dashboard', 'admin', 'proforma', 'property-manager', 'complaints']);
@endphp

<!-- Mobile Floating Footer - Only show when logged in -->
@if($currentUser)
<div class="mobile-floating-footer" id="mobileFloatingFooter">
    <div class="floating-footer-container">
        <div class="floating-nav-items">
            <!-- Home -->
            <a href="{{ url('/') }}" class="floating-nav-item {{ $currentSegment === null ? 'active' : '' }}">
                <div class="nav-icon">
                    <i class="bi bi-house{{ $currentSegment === null ? '-fill' : '' }}"></i>
                </div>
                <span class="nav-label">Home</span>
            </a>

            <!-- Dashboard (Quick Access) -->
            <a href="{{ url('/dashboard') }}"
                class="floating-nav-item {{ $currentSegment === 'dashboard' ? 'active' : '' }}">
                <div class="nav-icon">
                    <i class="bi bi-grid{{ $currentSegment === 'dashboard' ? '-fill' : '' }}"></i>
                </div>
                <span class="nav-label">Dash</span>
            </a>

            <!-- Role Specific Action -->
            @if($currentUser->isTenant())
                <a href="{{ url('/dashboard/payments') }}"
                    class="floating-nav-item {{ request()->is('dashboard/payments*') ? 'active' : '' }}">
                    <div class="nav-icon">
                        <i class="bi bi-credit-card{{ request()->is('dashboard/payments*') ? '-fill' : '' }}"></i>
                    </div>
                    <span class="nav-label">Pay</span>
                </a>
            @elseif($currentUser->isLandlord() || $currentUser->isAgent())
                <a href="{{ url('/dashboard/myproperty') }}"
                    class="floating-nav-item {{ request()->is('dashboard/myproperty*') ? 'active' : '' }}">
                    <div class="nav-icon">
                        <i class="bi bi-building{{ request()->is('dashboard/myproperty*') ? '-fill' : '' }}"></i>
                    </div>
                    <span class="nav-label">Props</span>
                </a>
            @endif

            <!-- Messages -->
            <a href="{{ url('/dashboard/messages/inbox') }}"
                class="floating-nav-item {{ request()->is('dashboard/messages*') ? 'active' : '' }}">
                <div class="nav-icon">
                    <i class="bi bi-chat-dots{{ request()->is('dashboard/messages*') ? '-fill' : '' }}"></i>
                    @php
                        $unreadCount = $currentUser->receivedMessages()->where('is_read', false)->count();
                    @endphp
                    @if($unreadCount > 0)
                        <span class="nav-badge">{{ $unreadCount }}</span>
                    @endif
                </div>
                <span class="nav-label">Chat</span>
            </a>

            <!-- Profile Dropdown for Mobile Footer -->
            <div class="dropdown">
                <a href="#" class="floating-nav-item dropdown-toggle" id="mobileFooterProfileDropdown" 
                   data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                   style="text-decoration: none;">
                    <div class="nav-icon">
                        @if($currentUser->photo)
                            <img src="/storage/{{ $currentUser->photo }}" alt="Profile"
                                style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover;">
                        @else
                            <i class="bi bi-person-circle"></i>
                        @endif
                    </div>
                    <span class="nav-label">Account</span>
                </a>
                <div class="dropdown-menu dropdown-menu-right mobile-footer-dropdown" aria-labelledby="mobileFooterProfileDropdown">
                    <a class="dropdown-item" href="{{ url('/dashboard/user') }}">
                        <i class="bi bi-person mr-2"></i> Profile
                    </a>
                    <a class="dropdown-item" href="{{ url('/dashboard') }}">
                        <i class="bi bi-speedometer2 mr-2"></i> Dashboard
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger" href="#" onclick="handleLogout('logout-form-mobile-footer')">
                        <i class="bi bi-box-arrow-right mr-2"></i> Logout
                    </a>
                </div>
            </div>
            <form id="logout-form-mobile-footer" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
    </div>
</div>
@endif

<!-- Floating Footer Spacer -->
<div class="floating-footer-spacer d-md-none"></div>

<style>
/* Mobile Footer Dropdown Styles */
.mobile-footer-dropdown {
    bottom: 80px !important;
    top: auto !important;
    border-radius: 12px;
    border: none;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    background: rgba(255,255,255,0.98);
    backdrop-filter: blur(10px);
    min-width: 200px;
    /* Override Bootstrap's inline transform */
    transform: translate3d(-50px, -2px, 0px) !important;
}

html[data-chrome-dark="true"] .mobile-footer-dropdown {
    background: rgba(30, 30, 30, 0.98) !important;
    box-shadow: 0 10px 30px rgba(0,0,0,0.5) !important;
}

html[data-chrome-dark="true"] .mobile-footer-dropdown .dropdown-item {
    color: #e2e8f0 !important;
}

html[data-chrome-dark="true"] .mobile-footer-dropdown .dropdown-item:hover {
    background: rgba(81, 203, 206, 0.2) !important;
}

html[data-chrome-dark="true"] .mobile-footer-dropdown .dropdown-divider {
    border-color: rgba(255, 255, 255, 0.1) !important;
}

.dropdown-toggle::after {
    display: none !important;
}
</style>

<script>
    $(document).ready(function () {
        // Show the footer with a slight delay
        setTimeout(function () {
            $('#mobileFloatingFooter').addClass('show');
        }, 500);

        // Keep footer always visible - no hiding on scroll for authenticated users
        @if(auth()->check())
            // Footer stays visible for logged-in users
            $('#mobileFloatingFooter').removeClass('scroll-hidden');
        @else
            // Optional: Hide on scroll down, show on scroll up for guests
            let lastScrollTop = 0;
            $(window).scroll(function (event) {
                let st = $(this).scrollTop();
                if (st > lastScrollTop && st > 100) {
                    // Scroll Down
                    $('#mobileFloatingFooter').addClass('scroll-hidden');
                } else {
                    // Scroll Up
                    $('#mobileFloatingFooter').removeClass('scroll-hidden');
                }
                lastScrollTop = st <= 0 ? 0 : st; // For Mobile or negative scrolling
            });
        @endif
    });
</script>