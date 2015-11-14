<!-- Stored in resources/views/layouts/master.blade.php -->

<html>
    <head>
        <title>mediadump</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
        <link rel="stylesheet" href="{{ elixir('css/all.css') }}">
    </head>
    <body ng-app="mediadump">
        <div ng-controller="MainUI">

            mediadump

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
               


                <ul class="nav navbar-nav navbar-right" ng-show="bLoggedIn">
                    <li><strong>logged in: {{--Auth::user()->name--}}</strong></li>
                    <li><a ng-click="logout()"><i class="fa fa-sign-out"></i> logout</a></li>
                </ul>

            </div>

            <div ng-show="bLoggedIn == false && !bSomethingLoading">
                @include('app.login')

            </div>

        </div>

        <div id="loading" ng-show="bSomethingLoading">
            <i class="fa fa-spinner fa-spin"></i> loading [todo: youtube style top bar]
        </div>

        <script type="text/javascript" src="{{ elixir('js/all.js') }}"></script>
    </body>
</html>