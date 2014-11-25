@extends('admin.admin-template')


@section('content')
    <!--<p>github style graph</p>-->
    <table>
    	<tbody>
    		<tr>
		   		<td>no of files</td><td>{{StatsController::iTotalFiles()}}</td>
		   	</tr>
		   	<tr>
		   		<td>no of live files</td><td>{{StatsController::iTotalLiveFiles()}}</td>
		   	</tr>
		   	<tr>
		   		<td>no of tags</td><td>{{StatsController::iTotalTags()}}</td>
		   	</tr>
		   	<tr>
		   		<td>last found</td><td>{{StatsController::iLastFoundFiles()}}</td>
		   	</tr>
		   	<tr>
		   		<td>current average process files</td><td>{{StatsController::iLastAverageProcessedFiles()}}</td>
		   	</tr>
		   	<tr>
		   		<td>current average process time</td><td>{{StatsController::iLastAverageProcessTimme()}}</td>
		</tbody>
	</table>
<hr/>
queue, total files
for each queue, count of files

current file processed per run

burn up or down over time

@stop