<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QaraTMS - Open Source Test Management Tool</title>

    <link rel="icon" type="image/x-icon" href="{{asset('/img/favicon.ico')}}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.min.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>
    <script src="{{asset('js/js.cookie.min.js')}}"></script>

    <link href="{{asset('css/main.css')}}" rel="stylesheet">

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

<div class="modal fade" id="any_img_lightbox" tabindex="-1" aria-labelledby="exampleModalCenterTitle"
     style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="position-absolute top-50 start-50 translate-middle">
            <img id="any_img_lightbox_image" src="" alt="">
        </div>
    </div>
</div>

<script src="{{asset('js/main.js')}}"></script>
@yield('footer')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm"
        crossorigin="anonymous"></script>
</body>
</html>
