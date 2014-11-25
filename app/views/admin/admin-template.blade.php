
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
		<!-- app specific-->
		<style>
			/*
			ADMIN
			*/
			.label.checkfiles{
				background:#3498db;
				color:#fff;
			}
			.label.jpegprocessor{
				background:#27ae60;
				color:#fff;
			}
			.label.unknown{
				background:#8e44ad;
				color:#fff;
			}
		</style>

		<link rel="stylesheet" type="text/css" href="/vendor/daterangepicker/daterangepicker-bs3.css" />

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

		<script type="text/javascript" src="/vendor/moment/moment.min.js"></script>
		<script type="text/javascript" src="/vendor/daterangepicker/daterangepicker.js"></script>

		<!-- bootstrap -->
		<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>

	    <script type="text/javascript">
			$(document).ready(function() {
				$("#fromto").daterangepicker(
					{
						format: 'DD-MM-YYYY',
						showDropdowns: true,
                    	separator: ' to ',
						ranges: {
	                       'Today': [moment(), moment()],
	                       'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
	                       'Last 7 Days': [moment().subtract(6, 'days'), moment()],
	                       'Last 30 Days': [moment().subtract(29, 'days'), moment()],
	                       'This Month': [moment().startOf('month'), moment().endOf('month')],
	                       'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
	                    }
					},
					function(start,end,label) {
						$("input[name=from]").val(start.format("DD-MM-YYYY"));
						$("input[name=to]").val(end.format("DD-MM-YYYY"));
					}
				);
			});
		</script>
		
	</body>
</html>