<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <title>@yield('title')</title>
    {{--<meta name="viewport" content="width=device-width, initial-scale=1">--}}
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <!--    <link rel="stylesheet" href="./assets/css/united/bootstrap.css" media="screen">-->
    {{ HTML::style('/assets/css/simplex/bootstrap.min.css') }}
    {{ HTML::style('/assets/css/default/bootstrap-editable.css') }}
    {{ HTML::style('/vendor/font-awesome/css/font-awesome.min.css') }}
    {{ HTML::style('/vendor/magicssuggest/magicsuggest-min.css') }}
    {{--{{ HTML::style('/vendor/perfect-scrollbar/perfect-scrollbar.min.css') }}--}}
    {{ HTML::style('/assets/css/app.css') }}
    @yield('style')
    
    <noscript>
     <meta http-equiv="refresh" content="0; url=/js" />
    </noscript>
    
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body id="top">
    @include('inc.nav')

    <div class="notification">
        {{ Notification::showAll() }}
    </div>

    <div class="container">
        <div class="content">
            @yield('content')
        </div>

        <hr/>

        <div>
            <p>&copy; Copyright 2014 YNKLabs. All rights reserved.</p>
        </div>
    </div>

    <div id="back-top"><!-- scroll top button -->
        <a href="#top"><span><i class="fa fa-arrow-circle-o-up fa-4x"></i></span>Yukarı Çık</a>
    </div>

    <div id="modal"></div>

    <!-- BEGIN: Underscore Modal Template Definition. -->
    <script type="text/template" class="template" id="modalTemplate">

        <div id="alertModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="alertModalLabel" aria-hidden="true">
            <div class="modal-dialog <%= rc.modal.type %>">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                        <h4 class="modal-title" id="alertModalLabel"><%= rc.modal.title %></h4>
                    </div>
                    <div class="modal-body">
                    <%= rc.modal.text %>
                </div>
                    <div class="modal-footer">
                        <% if(rc.modal.confirm) { %>
                        <input type="hidden" name="action" value=""/>
                        <button type="button" class="btn btn-danger btn-confirm" data-dismiss="modal" data-action="<%= rc.modal.action %>" data-input="<%= rc.modal.input %>">Tamam</button>
                        <% } %>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Kapat</button>
                    </div>
                </div>
            </div>
        </div>
    </script>
    <!-- END: Underscore Modal Template Definition. -->

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    {{ HTML::script('/assets/js/jquery-1.10.2.min.js') }}
    {{ HTML::script('/assets/js/bootstrap.min.js') }}
    {{ HTML::script('/assets/js/bootstrap-editable.min.js') }}
    {{ HTML::script('/vendor/purl/purl.min.js') }}
    {{ HTML::script('/vendor/underscore/underscore-min.js') }}
    {{ HTML::script('/vendor/date-format/jquery-dateFormat.min.js') }}
    {{ HTML::script('/vendor/multiselect/multiselect.min.js') }}
    {{ HTML::script('/vendor/magicssuggest/magicsuggest-min.js') }}
    {{--{{ HTML::script('/vendor/perfect-scrollbar/perfect-scrollbar.min.js') }}--}}
    {{ HTML::script('/vendor/kayalshri/tableExport.js') }}
    {{ HTML::script('/vendor/kayalshri/jquery.base64.js') }}
    {{ HTML::script('/vendor/kayalshri/html2canvas.js') }}
    {{ HTML::script('/vendor/kayalshri/jspdf/libs/sprintf.js') }}
    {{ HTML::script('/vendor/kayalshri/jspdf/jspdf.js') }}
    {{ HTML::script('/vendor/kayalshri/jspdf/libs/base64.js') }}
    {{ HTML::script('/assets/js/clipboard.min.js') }}
    {{ HTML::script('/assets/js/app.js') }}
    @yield('script')
</body>
</html>