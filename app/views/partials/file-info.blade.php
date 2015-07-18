
<?php
	$bGeoData = false;

	if(count($filedata) > 0)
	{
		if(isset($filedata[0]->latitude) && isset($filedata[0]->longitude))
			if(($filedata[0]->latitude !== 0) && ($filedata[0]->longitude !== 0))
				$bGeoData = true;
	}

?>
	@if($bGeoData)
		{{-- show map and tags --}}
		<div>
			<?php
				$iGeoOffset = 0.5;

				$oTag = [];
				$oTag["display"] = "map search";
				$oTag["value"] = "map=".($filedata[0]->latitude-$iGeoOffset).",".($filedata[0]->latitude+$iGeoOffset).",".($filedata[0]->longitude-$iGeoOffset).",".($filedata[0]->longitude+$iGeoOffset);

				$oaTags = [];
				array_push($oaTags, $oTag);

				$sHref = '#queries='. rawurlencode(json_encode($oaTags))."&mode=map";
			?>
			<a target="_blank" href="/{{$sHref}}">
				<img src="https://maps.googleapis.com/maps/api/staticmap?center={{$filedata[0]->latitude}},{{$filedata[0]->longitude}}&zoom=13&size=600x300&maptype=roadmap" />
			</a>
		</div>
	@endif

	<div id="urls">
		<strong>direct url</strong>
		<input class="form-control input-sm" type="text" value="{{URL::to('/thumbs/large/'.$filedata[0]->hash.'.jpg')}}">
	</div>

	<div>
	<?php
		$sLast = "";
	?>
	@foreach($filedata as $data)
		<?php
			$sClass = "tag";
			if($sLast !== $data->type){
				echo "<br/><strong>", str_replace(".", ": ", $data->type), "</strong><br/>";
				$sLast = $data->type;
			}
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
					$sClass = "place";
					break;
				case 'uniquedirectorypath':
					$sClass = "unique-directory-path";
					break;
			}

			$oTag = [];
			$oTag["display"] = $data->value;
			$oTag["value"] = $data->value;

			$oaTags = [];
			array_push($oaTags, $oTag);

			$sHref = '#queries='. urlencode(json_encode($oaTags));

		?>
		<a onclick="setSolitaryQuery('{{$data->value}}', '{{$data->value}}'); return false;" href="<?php echo $sHref; ?>" title="{{$data->confidence}}%"><span class="label {{$sClass}}">{{$data->value}}</span></a>
	@endforeach

</div>