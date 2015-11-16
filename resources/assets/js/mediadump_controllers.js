var mediadumpControllers = angular.module('mediadumpControllers', []);



mediadumpControllers.controller('MainUICtrl', ['$scope', '$rootScope', '$http', '$interval', '$location', function($scope, $rootScope, $http, $interval, $location) {


    $scope.sMDStatus = null;

	$scope.bLoggedIn = false;
	
	$rootScope.gblMDApp.bSomethingLoading = true;

	// login / register forms
	$scope.email = '';
	$scope.password = '';
	$scope.name = '';

    $scope.ping = function(){
        // set loading
        
    }

	$scope.login = function(){
		$scope.bSomethingLoading = true;
		// parse form and submit
		$http({
			method: "POST",
			url: "/app/auth/login",
			params: {
				'email': $scope.email,
				'password': $scope.password
			}
		}).then(function(response) {

			if(response.status == 200)
			{
				$scope.bLoggedIn = true;
				$scope.email = '';
				$scope.password = '';
                // now fetch items
                $scope.getItems();
                $(".feedback").html('');
			}
			// end loading
			$scope.bSomethingLoading = false;
		}, (function(response){
			$(".feedback").html(response.data);
			$scope.bSomethingLoading = false;
		}));
	};

	$scope.register = function(){
		$scope.bSomethingLoading = true;
		// parse form and submit
		$http({
			method: "POST",
			url: "/app/auth/register",
			params: {
				'email': $scope.register_email,
				'password': $scope.register_password
			}
		}).then(function(response) {

			if(response.status == 200)
			{
				$scope.bLoggedIn = true;
                // now fetch items
                $scope.getItems();
                $(".register_feedback").html('');
			}else{
                
			}
			// end loading
			$scope.bSomethingLoading = false;
		}, (function(response){
			$(".register_feedback").html(response.data);
			$scope.bSomethingLoading = false;
		}));
	};

	$scope.logout = function(){
		$scope.bSomethingLoading = true;
		// parse form and submit
		$http({
			method: "POST",
			url: "/app/auth/logout"
		})
		.success(function(response) {

			if(response.status == 200)
			{
				$scope.bLoggedIn = false;
			}
			// end loading
			$scope.bSomethingLoading = false;
			// user may be logged in or out now
			$scope.bLoggedIn = (response.status == 200 ? true : false);
		})
		.error(function(){
			$scope.bSomethingLoading = false;
		});
	};

    //$scope.ping();
}]);


mediadumpControllers.controller('SetupCtrl', ['$scope', '$routeParams',
  function($scope, $routeParams) {

  }]);


mediadumpControllers.controller('HeaderCtrl', ['$scope', '$rootScope', '$routeParams',
 	function($scope, $rootScope) {
		$scope.local = "local data";
  		//$scope.sMDStatus = $rootScope.sMDStatus;

  		$scope.datatest = $rootScope.test;

  		$scope.getMDApp = function(){
  			return $rootScope.gblMDApp;
  		}
  }]);