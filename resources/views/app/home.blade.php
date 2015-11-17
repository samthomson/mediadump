
<html ng-app="mediadumpApp">
    <head>
        <title>mediadump</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
        <link rel="stylesheet" href="{{ elixir('css/all.css') }}">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons"
      rel="stylesheet">
    </head>
    <body>

        <div ng-controller="HeaderCtrl">
            <md-progress-linear md-mode="indeterminate" ng-show="getMDApp().bSomethingLoading" id="app-load-state"></md-progress-linear>

            <div id="header">
                <button ng-ckick="home()" ng-disabled="getMDApp().state == 'empty'">mediadump</button> setup: @{{getMDApp().state}}, loading: @{{getMDApp().bSomethingLoading}}, logged in: @{{getMDApp().bLoggedIn}}

                <ul><li>login?</li></ul>
            </div>
            <hr/>
        </div>

        <div ng-view></div>

        <script type="text/javascript" src="{{ elixir('js/all.js') }}"></script>
    </body>
</html>