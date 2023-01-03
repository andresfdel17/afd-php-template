<!DOCTYPE html>
<html @php
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            echo 'lang=' . "'" . substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) . "'";
        } else {
            echo 'lang=' . "'" . config()->APP_LANG . "'";
        }
@endphp>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('page-title')</title>
    <meta name="title" content="Información">
    <meta name="keywords" content="">
    <meta name="description" content="">
    <meta name="msapplication-TileImage" content="{{ config()->APP_URL }}/Assets/Public?file=img/icon.png&type=img">
    <link rel="shortcut icon" href="{{ config()->APP_URL }}/Assets/Public?file=img/icon.ico&type=img" sizes="100x100">
    @yield('styles')
    @yield('scripts_top')
</head>
<body>
    <div id="login-div" class="main-content">@yield('content')</div>
    @yield('scripts_bottom')
</body>
</html>