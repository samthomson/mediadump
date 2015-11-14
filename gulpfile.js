var elixir = require('laravel-elixir');


elixir(function(mix) {
    mix.sass('app.scss',
    	'resources/assets/css');
});

elixir(function(mix) {
    mix.styles([
        /*"bootstrap.css",
        "bootstrap-theme.css",*/
        "app.css"
    ]);
});

elixir(function(mix) {
    mix.scripts([/*
        "jquery-2.1.4.min.js",
        "angular-1.4.3.min.js",*/
        "mediadump_app.js"/*,
        "bootstrap.min.js"*/
    ]);
});


elixir(function(mix) {
    mix.version(["css/all.css", "js/all.js"]);
});