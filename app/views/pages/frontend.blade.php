<!DOCTYPE html>
	<head>
		<title>mediadump: sam thomsons pictures &amp; videos</title>

		<!-- meta -->
    	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />

		<!-- css & fonts -->
		<!-- fonts -->
		<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
		
		<link href='http://fonts.googleapis.com/css?family=Muli' rel='stylesheet' type='text/css'>
		
		<!-- bootstrap -->
		<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">


		<link rel="stylesheet" href="/vendor/bootstraptags/bootstrap-tags.css">
		
		<!-- app specific -->
		<link rel="stylesheet" href="/css/style.css" type="text/css">
		<link rel="stylesheet" href="/css/font.css" type="text/css">
		<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico"/>

	</head>
	<body>

		<noscript>Sorry, this page uses Javascript, which isn't enabled in your browser. You need to turn it on.</noscript>

		<!-- the actual webpage -->
		<div id="mainBody" ng-controller="mediadumpCtrl">

			<div id="header" class="clearfix">
				<div class="" >

					<nav class="navbar navbar-default" role="navigation">
						<div class="container-fluid">
						<!-- Brand and toggle get grouped for better mobile display -->
							<div class="navbar-header">
								<button type="button" class="navbar-toggle collapsed pull-left" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
									<span class="sr-only">Toggle navigation</span>
									<span class="icon-bar"></span>
									<span class="icon-bar"></span>
									<span class="icon-bar"></span>
								</button>
								<a class="navbar-brand" onclick="javascript:home(); return false;" href="/"><i class="glyphicon glyphicon-home visible-xs pull-left"></i>&nbsp;mediadump</a>
							</div>

							<!-- Collect the nav links, forms, and other content for toggling -->
							<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
								<ul class="nav navbar-nav" id="header-navigation">
									<li><a class="browse-link" href="javascript:setModeFromUI('browse');">Browse</a></li>
									<li><a class="map-link" href="javascript:setModeFromUI('map');">Map</a></li>
									<li><a class="shuffle-link" href="javascript:shuffle();">Shuffle</a></li>
								</ul>
								
								<form class="navbar-form navbar-right nav-search-form" role="search">
									<div class="form-group">
										
										<div id="search-input" class="tag-list"></div>
										
										<div id="autocomplete"></div>
									</div>
								</form>

								
							</div><!-- /.navbar-collapse -->
						</div><!-- /.container-fluid -->
					</nav>

				</div>
			</div>


			<div id="main">

				<div id="nav" class="left_position">
					<div id="map-canvas"></div>
				</div>

				<div id="results" class="right_position">
					<!-- loading -->
					<div id="loading" class="centred-message"><i class="glyphicon glyphicon-refresh spin"></i> loading</div>

					<!-- thumb results (grid search) -->
					<div id="thumb_results">
					</div>

					<!-- browse -->
					<!--<div id="browse_tree" class="container">-->
					<div id="browse_tree" class="">
					</div>

				</div>
				<div id="pagination">
					<div class="container">
						<div id="map_pagination">
						</div>
						<div id="grid_pagination">
						</div>
					</div>
				</div>
			</div>

			<div id="lightbox" >
				<a class="lightbox_button" id="close_lightbox" href="javascript:closeLightbox();" title="press 'Esc' to close">
					<i class="icon icon-close"></i>
				</a>
				<a class="lightbox_button" id="left_lightbox" href="javascript:lightChange(-1);" title="press '&#8592;' to view previous">
					<i class="fa fa-angle-left"></i>
				</a>
				<a class="lightbox_button" id="right_lightbox" href="javascript:lightChange(1);" title="press '&#8594;' to view next">
					<i class="fa fa-angle-right"></i>
				</a>
				<a class="lightbox_button" id="info_lightbox" href="javascript:toggleInfo();" title="press 'i' to toggle file info">
					<i class="icon-tag"></i>
				</a>
				<div id="lightbox_contents">				
				
					<a href="javascript:lightChange(1);" class="lightbox-image">
						<img />
					</a>
				</div>
				<div id="lightbox_info_view"></div>
			</div>
		</div>

		<!-- scripts & analytics -->
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>


        <script src='//maps.googleapis.com/maps/api/js?sensor=false'></script>
		<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>



		<script src="/vendor/bootstraptags/bootstrap-tags.min.js"></script>

		<script src="/vendor/lazyload/lazyload.min.js"></script>


		<!-- app -->
		<script src="/js/mediadump.js?v8"></script>

		<script>
		  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

		  ga('create', '{{Helper::_AppProperty('sGATrackingCode')}}', 'auto');
		  ga('send', 'pageview');

		</script>
	</body>
</html>