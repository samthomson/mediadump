var mediadumpApp = angular
	.module('mediadumpApp', ['ngRoute', 'mediadumpControllers'])
    
	.config(['$httpProvider', function($httpProvider) {
		$httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
	}]);





mediadumpApp.config(['$routeProvider',
  function($routeProvider) {
    //console.log($routeProvider);
    $routeProvider.
      when('/', {
        templateUrl: '/app/partials/app-ui.html',
        controller: 'MainUICtrl'
      }).
      when('/setup', {
        templateUrl: '/app/partials/setup-process.html',
        controller: 'SetupCtrl'
      }).
      otherwise({
        redirectTo: '/'
      });
  }])

.run(function($rootScope, $http, $location) {
    $rootScope.rootdata = 'global';

    $rootScope.gblMDApp = {state: null, bSomethingLoading: true};


    $rootScope.bSomethingLoading = true;


    $http({
        method: "GET",
        url: "/app/ping"
    })
    .then(function(response) {
        $rootScope.gblMDApp.state = response.data.md_state;
            
    //$rootScope.gblMDData = response.data.md_state;

    //$rootScope.rootdata = response.data.md_state;
            //console.log("state: " + $scope.sMDStatus);
            
            if($rootScope.gblMDApp.state == "empty"){
              $location.path( "/setup" );
            }

            // end loading
            $rootScope.gblMDApp.bSomethingLoading = false;

    },(function(){
            $rootScope.gblMDApp.bSomethingLoading = false;
    }));
});



$(document).ready(function(){
    $('body').css("visibility", "visible");
});
