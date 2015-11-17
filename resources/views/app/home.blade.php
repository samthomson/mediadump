
<html ng-app="mediadumpApp">
    <head>
        <title>mediadump</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
        <link rel="stylesheet" href="{{ elixir('css/all.css') }}">

        <!-- font font(s) -->

        <link rel='stylesheet' id='g_font-css'  href='http://fonts.googleapis.com/css?family=Noto+Sans%3A400%2C700%2C400italic%2C700italic&#038;ver=3.5.1' type='text/css' media='all' />

        <link href='http://fonts.googleapis.com/css?family=PT+Sans+Caption:700' rel='stylesheet' type='text/css'>


        <!-- icon font(s) -->
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons"
      rel="stylesheet">
    </head>
    <body>

        <div ng-controller="HeaderCtrl">
            <md-progress-linear md-mode="indeterminate" ng-show="getMDApp().bSomethingLoading" id="app-load-state"></md-progress-linear>

            <div id="header">
                <button ng-ckick="home()" ng-disabled="getMDApp().state == 'empty'" class="header-font">mediadump</button> setup: @{{getMDApp().state}}, loading: @{{getMDApp().bSomethingLoading}}, logged in: @{{getMDApp().bLoggedIn}}

                <ul><li>login?</li></ul>
            </div>
            <hr/>
        </div>

        <div ng-view></div>

        <script type="text/javascript" src="{{ elixir('js/all.js') }}"></script>
    </body>
</html>