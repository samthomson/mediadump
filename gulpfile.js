var elixir = require('laravel-elixir');


elixir(function(mix) {
    mix.sass('app.scss',
    	'resources/assets/css'
    	);
});

elixir(function(mix) {
    mix.styles([
        "app.css",
    	'../../../bower_components/angular-material/angular-material.css',
    	'../../../bower_components/Materialize/dist/css/materialize.min.css'
    ]);
});

elixir(function(mix) {
    mix.scripts([
    	'../../../bower_components/jquery/dist/jquery.min.js',
        '../../../bower_components/angular/angular.min.js',
        '../../../bower_components/angular-route/angular-route.min.js',
        '../../../bower_components/angular-animate/angular-animate.min.js',
        '../../../bower_components/angular-aria/angular-aria.min.js',
        '../../../bower_components/angular-material/angular-material.min.js',
        'mediadump_app.js',
        'mediadump_controllers.js',
    	'../../../bower_components/Materialize/dist/js/materialize.min.js'
    ]);
});


elixir(function(mix) {
    mix.version(["css/all.css", "js/all.js"]);
});