<!DOCTYPE html>
<html>
<head>
    <title>Help Request</title>
	<style>
		body {
			font-family: "Lucida Sans", "Lucida Sans Regular", "Lucida Grande", "Lucida Sans Unicode", Geneva, Verdana, sans-serif;
			text-align: center;
		}
		h1 {
			color: #90B5FF;
		}
		p {
			color: #C18CD6;
		}
		.button {
			display: inline-block;
			padding: 8px 16px;
			border-radius: 4px;
			background-color: #90B5FF;
			color: #fff;
			text-decoration: none;
			font-weight: bold;
		}
		.logo {
			margin-top: 30px;
			max-width: 200px;
		}
	</style>
</head>
<body>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td style="text-align: center;">
				<h1>{{ $mailData['title'] }}</h1>
				<p>User name : {{ $mailData['name'] }} </p>
				<p>User email : {{ $mailData['email'] }} </p>
				<p>Summary of the issue : {{ $mailData['summary'] }} </p>
				<p>Details (if given) : {{ $mailData['details'] }} </p>
				<p>Page link : <a href="{{ $mailData['link'] }} ">{{ $mailData['link'] }}</a> </p>
				@if($path!=null)
				  <p>Screenshot of the issue : <img src="{{ $message->embed(public_path('../storage/app/'.$path)) }}" alt="LogoTime2Do" class="logo"></p>
				@else
				  <p>No screenshot of the issue</p>
				@endif
				<p>Good luck 🫡</p>
				<img src="{{ $message->embed(public_path('logo.png')) }}" alt="LogoTime2Do" class="logo">
			</td>
		</tr>
	</table>
</body>
</html>