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


mediadumpControllers.controller('SetupCtrl', ['$scope', '$rootScope', '$routeParams', '$http', '$location',
  function($scope, $rootScope, $routeParams, $http, $location) {

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
                $rootScope.gblMDApp.state = "setup";
                $location.path("/admin");
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

	
	$scope.getMDApp = function(){
		return $rootScope.gblMDApp;
	}

  }]);


mediadumpControllers.controller('AdminCtrl', ['$scope', '$rootScope', '$routeParams', '$http',
  function($scope, $rootScope, $routeParams, $http) {


    $scope.tabSection = $routeParams.tabSection;
    //$scope.accountInfo = Dropbox.accountInfo();


    $scope.formFeedback = '';

    $scope.addDropboxFolder = {
    	feedback: '',
    	tested: '',
    	folder: '',
    	loading: false
    };

	$scope.getMDApp = function(){
		return $rootScope.gblMDApp;
	}

	$scope.connectDropbox = function(){
		window.location.href = '/';
	};

	$scope.newDropboxFolder = '';


	$scope.addDropboxFileSource = function(){

	}

	$scope.testNewDropboxFileSource = function(){
		// send folder to back end service which will 'test' connecting to it via dropbox api, returning true or false? thus allowing user to add it knowing that mediadump will find files in it
		$scope.addDropboxFolder.loading = true;
		$http({
			method: "POST",
			url: "/app/filesources/dropbox/test",
			params: {
				'path': $scope.addDropboxFolder.folder
			}
		}).then(function(response) {
			// end loading
			$scope.addDropboxFolder.loading = false;
			if(response.status == 200)
			{
				// all good
				$scope.formFeedback = '';
				$scope.addDropboxFolder.tested = response.data.testedPath;
			}else{
				$scope.addDropboxFolder.tested = false;
				$scope.formFeedback = response.data;
			}
		}, (function(response){
			$scope.addDropboxFolder.loading = false;
			$scope.formFeedback = response.data;
		}));
	}

  }]);


mediadumpControllers.controller('LoginCtrl', ['$scope', '$rootScope', '$routeParams', '$http', '$location',
  function($scope, $rootScope, $routeParams, $http, $location) {

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
		})
		.then(function(response) {
       		$scope.formFeedback = '';
            $scope.bSomethingLoading = false;
			$scope.bLoginLoading = false;
            $scope.formFeedback = '';

       		console.log(response.status);
       		if(response.status == 200)
       		{
       			$rootScope.gblMDApp.bLoggedIn = true;

				$location.path( "/admin" );
				//$rootScope.$apply();
       			
       		}
        }, function(response) {
        	$scope.formFeedback = '';
            $scope.bSomethingLoading = false;
			$scope.bLoginLoading = false;
			$scope.formFeedback = '';
      });

	};

}]);

mediadumpControllers.controller('HeaderCtrl', ['$scope', '$rootScope', '$routeParams', '$location', '$http',
 	function($scope, $rootScope, $routeParams, $location, $http) {
		$scope.local = "local data";

  		$scope.datatest = $rootScope.test;

  		$scope.getMDApp = function(){
  			return $rootScope.gblMDApp;
  		}



		$scope.logout = function(){
			$scope.bSomethingLoading = true;
			// parse form and submit
			$http({
				method: "POST",
				url: "/app/auth/logout"
			})
			.success(function(response) {
			
	
				$rootScope.gblMDApp.bLoggedIn = false;

			    $location.path('/');

				// end loading
				$scope.bSomethingLoading = false;
				// user may be logged in or out now
				$rootScope.gblMDApp.bLoggedIn = (response.status == 200 ? true : false);
			})
			.error(function(){
				$scope.bSomethingLoading = false;
			});
		};
  }]);