var app = angular
	.module('mediadump', [])
	.config(['$httpProvider', function($httpProvider) {
		$httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
	}]);

// css hide 'bad' images
app.directive('imageonerror', function() {
    return {
        restrict: 'A',
        link: function(scope, element, attrs) {
            element.bind('error', function() {
            	$(element).css("background", "purple");
            	$(element).css("display", "block");
            });
        }
    };
});

app.controller('MainUI', function($scope, $http, $interval) {



	$scope.bLoggedIn = false;
	$scope.bSomethingLoading = true;

	// login / register forms
	$scope.email = '';
	$scope.password = '';
	$scope.name = '';

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

});

$(document).ready(function(){
	$('body').css("visibility", "visible");
});