<!-- Stored in resources/views/layouts/master.blade.php -->

<html ng-app="mediadumpApp">
    <head>
        <title>mediadump</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
        <link rel="stylesheet" href="{{ elixir('css/all.css') }}">
    </head>
    <body>
        <!--<div ng-controller="MainUI">-->
        <div>

            mediadump
            <div ng-view></div>             


        </div>

        <script type="text/javascript" src="{{ elixir('js/all.js') }}"></script>
    </body>
</html>