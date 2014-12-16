
<?php
	$bGeoData = false;

	if(count($filedata) > 0)
	{
		if(isset($filedata[0]->latitude) && isset($filedata[0]->longitude))
			if(($filedata[0]->latitude !== 0) && ($filedata[0]->longitude !== 0))
				$bGeoData = true;
	}

?>
	<div class="row">
	@if($bGeoData)
		{{-- show map and tags --}}
		<div class="col-xs-6">
			<img src="https://maps.googleapis.com/maps/api/staticmap?center={{$filedata[0]->latitude}},{{$filedata[0]->longitude}}&zoom=13&size=600x300&maptype=roadmap" />
		</div>
		<div class="col-xs-6">
	@else
		{{-- only tags --}}
		<div class="col-xs-6">
	@endif
	@foreach($filedata as $data)
		<?php
			$sClass = "tag";
			switch ($data->type) {
				case 'exif.cameramake':
				case 'exif.datetime':
					$sClass = "exif";
					break;
				case 'filename':
					$sClass = "filename";
					break;
				case 'filetype':
					$sClass = "filetype";
					break;
				case 'folder term':
					$sClass = "folder";
					break;
				case 'imagga':
					$sClass = "imagga";
					break;
				case 'mediatype':
					$sClass = "mediatype";
					break;
				case 'places.addresscomponent':
				case 'places.formattedaddress':
					$sClass = "places";
					break;
				case 'uniquedirectorypath':
					$sClass = "unique-directory-path";
					break;
			}
		?>
		<span class="label {{$sClass}}">{{$data->type}} : {{$data->value}}</span>	
	@endforeach

		</div>

</div>