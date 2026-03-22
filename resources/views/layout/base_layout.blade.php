<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QaraTMS - Open Source Test Management System</title>
    <link rel="icon" type="image/x-icon" href="{{asset('/img/favicon.ico')}}">
    <link href="{{asset('libs/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('libs/bootstrap-icons.min.css')}}" rel="stylesheet">
{{--    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">--}}
    <link href="{{asset('css/main.css')}}" rel="stylesheet">
    <script src="{{asset('libs/jquery-3.6.0.min.js')}}"></script>
    <script src="{{asset('libs/jquery-ui.min.js')}}"></script>
    <script src="{{asset('libs/js.cookie.min.js')}}"></script>
    @yield('head')
</head>
<body>
<div class="row sticky-top">
    @include('layout.header_nav')
</div>
<div class="container-fluid">
    <div class="row fh">
        @yield('content')
    </div>
</div>

<div class="modal fade" id="any_img_lightbox" tabindex="-1" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="position-absolute top-50 start-50 translate-middle">
            <img id="any_img_lightbox_image" src="" alt="">
        </div>
    </div>
</div>

{{--<div class="test_case_overlay-modal modal fade" id="test_case_overlay" tabindex="-1" style="display: none;" aria-hidden="true">--}}
{{--    <div class="modal-dialog modal-xl" id="test_case_overlay_data">--}}
{{--    </div>--}}
{{--</div>--}}

<script src="{{asset('js/main.js')}}"></script>

@yield('footer')
<script src="{{asset('libs/bootstrap.bundle.min.js')}}"></script>
</body>
</html>
