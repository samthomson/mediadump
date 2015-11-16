var elixir = require('laravel-elixir');


elixir(function(mix) {
    mix.sass('app.scss',
    	'resources/assets/css');
});

elixir(function(mix) {
    mix.styles([
        "app.css"
    ]);
});

elixir(function(mix) {
    mix.scripts([
    	'../../../bower_components/jquery/dist/jquery.min.js',
        '../../../bower_components/angular/angular.min.js',
        '../../../bower_components/angular-route/angular-route.min.js',
        'mediadump_app.js',
        'mediadump_controllers.js'
    ]);
});


elixir(function(mix) {
    mix.version(["css/all.css", "js/all.js"]);
});