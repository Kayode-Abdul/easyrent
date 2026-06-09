<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EasyRent Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Dropzone.js -->
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
    <link href="{{ asset('assets/css/currency-scroll.css') }}" rel="stylesheet" />
    <style>
        .dropzone {
            border: 2px dashed #007bff !important;
            border-radius: 10px;
            background: #f8f9fa !important;
            padding: 20px !important;
            min-height: 150px !important;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .dropzone:hover {
            background: #e9ecef !important;
            border-color: #0056b3 !important;
        }
        .dropzone .dz-message {
            margin: 0 !important;
        }
        .dropzone .dz-message .icon {
            font-size: 3rem;
            color: #007bff;
            margin-bottom: 10px;
        }
    </style>
    <!-- Include minimal CSS expecting header/footer already add dashboard CSS -->
    @stack('head')
</head>
<body>
@include('header')
    <script>window.currencySymbol = "{!! format_money(0)->getSymbol() !!}";</script>
@yield('content')
@include('footer')
@stack('scripts')
<script src="{{ asset('assets/js/currency-scroll.js') }}"></script>
</body>
</html>
