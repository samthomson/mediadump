var mediadumpApp = angular
	.module('mediadumpApp', ['ngRoute', 'mediadumpControllers']);
    /*
	.config(['$httpProvider', function($httpProvider) {
		$httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
	}]);*/

/*
// css hide 'bad' images
mediadumpApp.directive('imageonerror', function() {
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
*/


$(document).ready(function(){
	$('body').css("visibility", "visible");
});





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
  }]);