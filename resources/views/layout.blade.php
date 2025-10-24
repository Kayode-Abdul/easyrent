<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EasyRent Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Include minimal CSS expecting header/footer already add dashboard CSS -->
    @stack('head')
</head>
<body>
@include('header')
@yield('content')
@include('footer')
@stack('scripts')
</body>
</html>
