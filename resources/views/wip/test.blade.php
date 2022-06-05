<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

{{--    <link href="https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />--}}

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

    <link href="{{asset('repository/treeSortable.css')}}" rel="stylesheet">
</head>
<body>

<h1>Test</h1>



<div class="container">
    <ul id="tree">
        <li>werrewr</li>
        <li>werwer</li>
        <li>ewrewr</li>
        <li>werrwe</li>
        <li>werewrw</li>
    </ul>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>


<script src="{{asset('repository/treeSortable.js')}}"></script>
<script src="{{asset('repository/script.js')}}"></script>
</body>
</html>
