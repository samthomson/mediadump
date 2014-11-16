
<!DOCTYPE html>
	<head>
		<title>mediadump admin</title>

		<!-- meta -->
    	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />

		<!-- css & fonts -->
		<!-- fonts -->
		<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
		<link href='http://fonts.googleapis.com/css?family=Muli' rel='stylesheet' type='text/css'>
		<!-- bootstrap -->
		<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
		<!-- app specific
		<link rel="stylesheet" href="/css/style.css" type="text/css"> -->



	</head>
	<body ng-app="adminApp">

		<noscript>Sorry, this page uses Javascript, which isn't enabled in your browser. You need to turn it on.</noscript>

		<!-- the actual webpage -->
		<div id="mainBody" ng-controller="adminCtrl" class="container">

			<h2>Auto</h2>

			<div role="tabpanel">

				<!-- Nav tabs -->
				<ul class="nav nav-tabs" role="tablist">
					<li role="presentation" class="active"><a href="/admin" aria-controls="daterange">overview</a></li>
					<li role="presentation"><a href="/admin/events" aria-controls="granular">events</a></li>
				</ul>

				<div class="admin-body-content">
					 @yield('content')
				</div>
			</div>


		</div>

		<!-- scripts & analytics -->
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>

		<!-- angular 
		<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.2.10/angular.min.js"></script>-->
		<script src="/js/angular_1_2_fallback.min.js"></script>
		<script src="/vendor/bootstrap-ui/datepicker/datepicker.js"></script>
        <script src="//ajax.googleapis.com/ajax/libs/angularjs/1.2.10/angular-route.js"></script>

		<!-- bootstrap -->
		<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>

		<!-- app -->
		<script src="/js/adminApp.js"></script>

	</body>
</html>