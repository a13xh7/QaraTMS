<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="AF-TMS - Open Source Test Management System">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AF-TMS - Supported by QaraTMS</title>
    <link rel="icon" type="image/x-icon" href="{{asset('/img/favicon.ico')}}">
    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="{{ asset_path('css/main.css') }}" rel="stylesheet">
    <link href="{{ asset_path('css/dashboard.css') }}" rel="stylesheet">
    <script src="{{ asset_path('js/js.cookie.min.js') }}"></script>
    @yield('head')
</head>

<body>
    <div id="full-page-loading-overlay" style="
        display: flex;          {{-- Show by default --}}
        position: fixed;        {{-- Fixed position --}}
        top: 0;                 {{-- Cover top edge --}}
        left: 0;                {{-- Cover left edge --}}
        width: 100%;            {{-- Full width --}}
        height: 100%;           {{-- Full height --}}
        background: #fff;       {{-- White background (or any color) --}}
        z-index: 10000;         {{-- Make sure it's on top of everything --}}
        justify-content: center; {{-- Center content horizontally --}}
        align-items: center;     {{-- Center content vertically --}}
        flex-direction: column; {{-- Stack content --}}
        transition: opacity 0.5s ease-out; {{-- Optional: smooth fade out effect --}}
        opacity: 1;             {{-- Start with full opacity --}}
    ">
        <img src="{{ asset('/img/logo.png') }}" alt="Loading..." style="width: 120px; height: 80px;">
    </div>

    <div class="row sticky-top">
        @include('layout.header_nav')
    </div>
    <div class="container-fluid">
        <div class="row fh">
            @yield('content')
        </div>
    </div>

    <script>
        window.addEventListener('load', function() {
            const loadingOverlay = document.getElementById('full-page-loading-overlay');
            if (loadingOverlay) {
                setTimeout(function() {
                     loadingOverlay.style.opacity = '0'; // Start fade out
                     // Hide after transition is complete
                     loadingOverlay.addEventListener('transitionend', function() {
                         loadingOverlay.style.display = 'none';
                     }, { once: true });
                }, 200);
            }
        });
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- @yield('scripts') -->
    <!-- @yield('footer') -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
    <script src="{{ asset_path('js/main.js') }}"></script>
    <script src="{{ asset_path('js/js.cookie.min.js') }}"></script>
    @yield('scripts')
    @yield('footer')
    @stack('scripts')
</body>

</html>
<!-- 
@section('scripts')
<script>
console.log('Decision Logs custom script loaded!');
</script>
@endsection -->
