<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>@yield('title')</title>

    <!-- Bootstrap core CSS -->
    {{ HTML::style('/assets/css/simplex/bootstrap.min.css', array()) }}

    <!-- Custom styles for this template -->
    {{ HTML::style('/assets/css/auth.css', array()) }}
    {{ HTML::style('/assets/css/app.css', array()) }}

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
    <div class="container">

        <div class="notification">
            {{ Notification::showAll() }}
        </div>

        @yield('content')

    </div> <!-- /container -->

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    {{ HTML::script('/assets/js/jquery-1.10.2.min.js', array()) }}
    {{ HTML::script('/assets/js/bootstrap.min.js', array()) }}
    {{ HTML::script('/vendor/purl/purl.min.js') }}
    {{ HTML::script('/assets/js/app.js', array()) }}
</body>
</html>
