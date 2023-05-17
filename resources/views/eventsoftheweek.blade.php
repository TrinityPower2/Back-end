<!DOCTYPE html>
<html>
<head>
    <title>Weekly Schedule</title>
	<style>
		body {
			font-family: "Lucida Sans", "Lucida Sans Regular", "Lucida Grande", "Lucida Sans Unicode", Geneva, Verdana, sans-serif;
			text-align: center;
		}
		h1, h2 {
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
				<h1>Hello {{ $mailData['name'] }} ! Here is a reminder of your events scheduled for the 7 following days :</h1>
                @if($eventsPerWeek[0] != [])
                    <h2>Monday :</h2>
                    @foreach($eventsPerWeek[0] as $event)
                    <p>Name of the event : {{ $event->name_event }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Description of the event : {{ $event->description }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Start date : {{ $event->start_date }} </p>
                    @endforeach
                @endif
                @if($eventsPerWeek[1] != [])
                    <h2>Tuesday :</h2>
                    @foreach($eventsPerWeek[1] as $event)
                    <p>Name of the event : {{ $event->name_event }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Description of the event : {{ $event->description }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Start date : {{ $event->start_date }} </p>
                    @endforeach
                @endif
                @if($eventsPerWeek[2] != [])
                    <h2><br>Wednesday :</h2>
                    @foreach($eventsPerWeek[2] as $event)
                    <p>Name of the event : {{ $event->name_event }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Description of the event : {{ $event->description }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Start date : {{ $event->start_date }} </p>
                    @endforeach
                @endif
                @if($eventsPerWeek[3] != [])
                    <h2><br>Thursday :</h2>
                    @foreach($eventsPerWeek[3] as $event)
                    <p>Name of the event : {{ $event->name_event }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  Description of the event : {{ $event->description }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Start date : {{ $event->start_date }} </p>
                    @endforeach
                @endif
                @if($eventsPerWeek[4] != [])
                    <h2><br>Friday :</h2>
                    @foreach($eventsPerWeek[4] as $event)
                    <p>Name of the event : {{ $event->name_event }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Description of the event : {{ $event->description }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Start date : {{ $event->start_date }} </p>
                    @endforeach
                @endif
                @if($eventsPerWeek[5] != [])
                    <h2><br>Saturday :</h2>
                    @foreach($eventsPerWeek[5] as $event)
                    <p>Name of the event : {{ $event->name_event }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Description of the event : {{ $event->description }}  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Start date : {{ $event->start_date }} </p>
                    @endforeach
                @endif
                @if($eventsPerWeek[6] != [])
                    <h2><br>Sunday :</h2>
                    @foreach($eventsPerWeek[6] as $event)
                    <p>Name of the event : {{ $event->name_event }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Description of the event : {{ $event->description }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Start date : {{ $event->start_date }} </p>
                    @endforeach
                @endif
				<img src="{{ $message->embed(public_path('logo.png')) }}" alt="LogoTime2Do" class="logo">
			</td>
		</tr>
	</table>
</body>
</html>