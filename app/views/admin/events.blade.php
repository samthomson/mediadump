@extends('admin.admin-template')


@section('content')
    <form method="post" role="form">
    	<div class="form-group">
		    <label for="exampleInputEmail1">Date range:</label>
		    <input type="daterange" id="fromto" class="input-small form-control" value="{{$from or date('d/m/Y', strtotime('-1 month', time()))}} to {{$to or date('d/m/Y', strtotime('+1 day', time()))}}" />
		    <input type="hidden" name="from" value="{{$from or date('d/m/Y', strtotime('-1 month', time()))}}"/>
		    <input type="hidden" name="to" value="{{$to or date('d/m/Y', strtotime('+1 day', time()))}}" />
		</div>
    	<div class="form-group">
		    <input type="submit" class="btn btn-default form-control" value="show events"/>
		</div>


    	
    </form>
    <hr/>
   	
    @if(isset($events))
	    @if(count($events) > 0)
	    	<table class="table">
				<thead>
					<tr>
						<th>datetime</th>
						<th>name</th>
						<th>message</th>
						<th>value</th>					
					</tr>
				</thead>
				<tbody>
					@foreach($events as $event)
					<?php
						$sLabelClass = "";
						switch ($event->name) {
							case 'auto files checker ran':
								$sLabelClass = "checkfiles";
								break;
							case 'auto jpeg processor':
								$sLabelClass = "jpegprocessor";
								break;							
							default:
								$sLabelClass = "unknown";
								break;
						}
					?>
					<tr>
						<td>{{$event->datetime}}</td>
						<td><span class="label {{$sLabelClass}}">{{$event->name}}</span></td>
						<td>{{$event->message}}</td>
						<td>{{$event->value}}</td>
					</tr>
					@endforeach
				</tbody>
			</table>
	    @else
	    	<p>No events for that date range ..</p>
	    @endif
    @endif

@stop