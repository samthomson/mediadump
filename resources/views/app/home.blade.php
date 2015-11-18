
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
        <!--<link href="https://fonts.googleapis.com/icon?family=Material+Icons"
      rel="stylesheet">-->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    </head>
    <body>

        <div ng-controller="HeaderCtrl">
            <md-progress-linear md-mode="indeterminate" ng-show="getMDApp().bSomethingLoading" id="app-load-state"></md-progress-linear>

            <div id="header">

                <!--setup: @{{getMDApp().state}}, loading: @{{getMDApp().bSomethingLoading}}, logged in: @{{getMDApp().bLoggedIn}}-->

                <div class="ui secondary pointing menu">

                    <a ng-click="home()" ng-hide="getMDApp().state == 'empty'" class="header-font">mediadump</a>

                    <a class="item active">
                        browse
                    </a>
                    <a class="item">
                        explore
                    </a>
                    <a class="item">
                        map
                    </a>
                    <a class="item">
                        shuffle
                    </a>
                    <a class="item"><span ng-show="getMDApp().bLoggedIn">logged in</span><span ng-show="!getMDApp().bLoggedIn">logged out</span></a>
                    <div class="right menu">
                        <a class="ui item" ng-href="/#/admin">manage</a>
                    </div>
                </div>

            </div>
            <hr/>
        </div>

        <div ng-view></div>

        <script type="text/javascript" src="{{ elixir('js/all.js') }}"></script>
    </body>
</html>