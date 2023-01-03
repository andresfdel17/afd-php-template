<!DOCTYPE html>

<html @php
    if (!App\Lib\Session::sessionValidator([], true)) {
        echo 'lang=' . "'" . substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) . "'";
    } else {
        echo 'lang=' . "'" . $_SESSION['language'] . "'";
    }
@endphp>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$title}}</title>
    <meta name="title" content="Vmers Colombia">
    <meta name="keywords" content="">
    <meta name="description" content="">
    <link rel="shortcut icon" href="{{ config()->APP_URL }}/Assets/Public?file=img/icon.ico&type=img" sizes="100x100">
    <link href="{{ config()->APP_URL }}/Assets/Public/?file=css/AdminPages/404/bootstrap.min.css&type=css"
        rel="stylesheet">
    <link href="{{ config()->APP_URL }}/Assets/Public/?file=css/AdminPages/404/fonts.css&type=css" rel="stylesheet">
    <link href="{{ config()->APP_URL }}/Assets/Public/?file=css/AdminPages/404/styles.css&type=css" rel="stylesheet">
</head>

<body>
    <!-- partial:index.partial.html -->
    <section class="page_404">
        <div class="container">
            <div class="row">
                <div class="col-sm-12 ">
                    <div class="col-sm-10 col-sm-offset-1  text-center">
                        <div class="four_zero_four_bg">
                            <h1 class="text-center ">404</h1>
                        </div>
                        <div class="contant_box_404">
                            <h3 class="h2">
                                {{text("page-404-title")}}
                            </h3>
                            <p>{{text("page-404-text")}}</p>
                            <a href="/{{config()->HOME}}" class="link_404">{{text("page-404-btn")}}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- partial -->

</body>

</html>
