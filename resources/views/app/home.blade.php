
<html ng-app="mediadumpApp">
    <head>
        <title>mediadump</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
        <link rel="stylesheet" href="{{ elixir('css/all.css') }}">
    </head>
    <body>

        <div ng-controller="HeaderCtrl">
            <a ng-href="/" ng-disabled="true">mediadump</a> mdstatus: state: @{{getMDApp().state}}, loading: @{{getMDApp().bSomethingLoading}}
            <hr/>
        </div>

        <div ng-view></div>

        <script type="text/javascript" src="{{ elixir('js/all.js') }}"></script>
    </body>
</html>