@extends('admin.admin-template')


@section('content')
    <form method="post" role="form">
    	<div class="form-group">
		    <label for="exampleInputEmail1">Date range:</label>
		    <input type="daterange" id="fromto" class="input-small form-control" value="<?php echo date('d/m/Y', strtotime('-1 month', time()))." - ".date('d/m/Y') ?>" />
		    <input type="hidden" name="from" />
		    <input type="hidden" name="to" />
		</div>
    	<div class="form-group">
		    <input type="submit" class="btn btn-default form-control" value="show events"/>
		</div>


    	
    </form>
    <hr/>
   	
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
				<tr>
					<td>{{$event->datetime}}</td>
					<td>{{$event->name}}</td>
					<td>{{$event->message}}</td>
					<td>{{$event->value}}</td>
				</tr>
				@endforeach
			</tbody>
		</table>
    @else
    	<p>No events for that date range ..</p>
    @endif

@stop