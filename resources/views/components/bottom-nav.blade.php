{{-- Bottom Navigation for Mobile --}}
@auth
<nav class="bottom-nav d-md-none">
    <a href="{{ route('home') }}" class="bottom-nav-item {{ request()->routeIs('home') ? 'active' : '' }}">
        <i class="fa fa-home"></i>
        <span>Home</span>
    </a>
    
    <a href="{{ route('properties') }}" class="bottom-nav-item {{ request()->routeIs('properties') ? 'active' : '' }}">
        <i class="fa fa-building"></i>
        <span>Properties</span>
    </a>
    
    <a href="{{ route('dashboard') }}" class="bottom-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="fa fa-th-large"></i>
        <span>Dashboard</span>
    </a>
    
    <a href="{{ route('messages.inbox') }}" class="bottom-nav-item {{ request()->routeIs('messages.*') ? 'active' : '' }}">
        <i class="fa fa-envelope"></i>
        <span>Messages</span>
        @php
            $unreadCount = Auth::user()->receivedMessages()->where('is_read', false)->count();
        @endphp
        @if($unreadCount > 0)
            <span class="badge">{{ $unreadCount }}</span>
        @endif
    </a>
    
    <a href="{{ route('user.profile') }}" class="bottom-nav-item {{ request()->routeIs('user.*') ? 'active' : '' }}">
        <i class="fa fa-user"></i>
        <span>Profile</span>
    </a>
</nav>
@endauth

{{-- Floating Action Button --}}
@auth
@if(request()->routeIs('dashboard') || request()->routeIs('myProperty'))
<button class="fab d-md-none" onclick="document.getElementById('addPropertyBtn')?.click() || window.location.href='{{ route('property.add') }}'">
    <i class="fa fa-plus"></i>
</button>
@endif
@endauth
