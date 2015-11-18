var mediadumpApp = angular
	.module('mediadumpApp', ['ngRoute', 'mediadumpControllers', 'ngMaterial'])
    
	.config(['$httpProvider', function($httpProvider) {
		$httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
	}]);





mediadumpApp.config(['$routeProvider',
  function($routeProvider) {
    // routing
    $routeProvider.
      when('/', {
        templateUrl: '/app/partials/app-ui.html',
        controller: 'MainUICtrl'
      }).
      when('/setup', {
        templateUrl: '/app/partials/setup-process.html',
        controller: 'SetupCtrl'
      }).
      when('/admin', {
        templateUrl: '/app/partials/admin-backend.html',
        controller: 'AdminCtrl'
      }).
      when('/login', {
        templateUrl: '/app/partials/login.html',
        controller: 'LoginCtrl'
      }).
      otherwise({
        redirectTo: '/'
      });
  }])

.run(function($rootScope, $http, $location) {
    $rootScope.rootdata = 'global';

    $rootScope.gblMDApp = {
        state: null,
        bSomethingLoading: true,
        bLoggedIn: false
    };


    $rootScope.bSomethingLoading = true;


    $http({
        method: "GET",
        url: "/app/ping"
    })
    .then(function(response) {
        $rootScope.gblMDApp.state = response.data.md_state;
        $rootScope.gblMDApp.bLoggedIn = response.data.bLoggedIn;
            
            
        if($rootScope.gblMDApp.state == "empty"){
            $location.path( "/setup" );
        }

        // end loading
        $rootScope.gblMDApp.bSomethingLoading = false;

    },(function(){
        $rootScope.gblMDApp.bSomethingLoading = false;
    }));

    // register listener to watch route changes, create a front side admin 'check'
    $rootScope.$on( "$routeChangeStart", function(event, next, current) {
        console.log("going to: " + next.templateUrl);
        /*
        if ( $rootScope.gblMDApp.bLoggedIn !== true ) {
            // no logged user, we should be going to #login
            if ( next.templateUrl == "partials/login.html" ) {
                // already going to #login, no redirect needed
            } else {
                // not going to #login, we should redirect now
                $location.path( "/login" );
            }
        }
        */     
    });
})

.filter("sanitize", ['$sce', function($sce) {
  return function(htmlCode){
    return $sce.trustAsHtml(htmlCode);
  }
}]);