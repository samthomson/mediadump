var mediadumpControllers = angular.module('mediadumpControllers', []);



mediadumpControllers.controller('MainUICtrl', ['$scope', '$rootScope', '$http', '$interval', '$location', function($scope, $rootScope, $http, $interval, $location) {


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

	$scope.getMDApp = function(){
		return $rootScope.gblMDApp;
	}

}]);


mediadumpControllers.controller('SetupCtrl', ['$scope', '$rootScope', '$routeParams', '$http',
  function($scope, $rootScope, $routeParams, $http) {

	// login / register forms
	$scope.email = '';
	$scope.password = '';
	$scope.name = '';

	$scope.setup_user = {
		name: "",
		email: "",
		password: "",
		password_confirmation: "",
		defaultToPublic: 1
	};

	$scope.bSetupLoading = false;
	$scope.formFeedback = '';

	$scope.setupMediaDump = function()
	{
		$scope.bSetupLoading = true;
		$scope.bSomethingLoading = true;

		$http({
			method: "POST",
			url: "/app/auth/setup",
			params: {
				'email': $scope.setup_user.email,
				'password': $scope.setup_user.password,
				'password_confirmation': $scope.setup_user.password_confirmation,
				'name': $scope.setup_user.name
			}
		}).then(function(response) {

			if(response.status == 200)
			{
				// all good, md was set up okay, and the user got logged in (according to the backend..)
                $rootScope.gblMDApp.bLoggedIn = true;
                $scope.formFeedback = '';
			}else{

			}
			// end loading
			$scope.bSomethingLoading = false;
			$scope.bSetupLoading = false;
		}, (function(response){
			$scope.formFeedback = response.data;
			$scope.bSomethingLoading = false;
			$scope.bSetupLoading = false;
		}));
	}

	

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


	$scope.getMDApp = function(){
		return $rootScope.gblMDApp;
	}

  }]);


mediadumpControllers.controller('AdminCtrl', ['$scope', '$rootScope', '$routeParams', '$http',
  function($scope, $rootScope, $routeParams, $http) {


	$scope.getMDApp = function(){
		return $rootScope.gblMDApp;
	}

  }]);


mediadumpControllers.controller('LoginCtrl', ['$scope', '$rootScope', '$routeParams', '$http',
  function($scope, $rootScope, $routeParams, $http) {

  	$scope.bLoginLoading = false;
	$scope.formFeedback = '';

	$scope.getMDApp = function(){
		return $rootScope.gblMDApp;
	}

	$scope.login_data = {
		email: "",
		password: ""
	};

	$scope.login = function(){
		$scope.bLoginLoading = true;
		$scope.bSomethingLoading = true;
		// parse form and submit
		$http({
			method: "POST",
			url: "/app/auth/login",
			params: {
				'email': $scope.login_data.email,
				'password': $scope.login_data.password
			}
		}).then(function(response) {

			if(response.status == 200)
			{
				$rootScope.gblMDApp.bLoggedIn = true;
				$location.path( "#/admin" );
				$scope.login_data.email = '';
				$scope.login_data.password = '';
                // now fetch items
                $scope.formFeedback = '';
			}
			// end loading
			$scope.bSomethingLoading = false;
			$scope.bLoginLoading = false;
		}, (function(response){
            // login failed, relay error to user
            $scope.formFeedback = response.data;
            $scope.bSomethingLoading = false;
			$scope.bLoginLoading = false;
		}));
	};

}]);

mediadumpControllers.controller('HeaderCtrl', ['$scope', '$rootScope', '$routeParams', '$location',
 	function($scope, $rootScope, $routeParams, $location) {
		$scope.local = "local data";

  		$scope.datatest = $rootScope.test;

  		$scope.getMDApp = function(){
  			return $rootScope.gblMDApp;
  		}
  }]);