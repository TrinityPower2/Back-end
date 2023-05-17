<!DOCTYPE html>
<html>
<head>
	<title>Welcome to Time to Do!</title>
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
			<h1>Welcome to Time to Do!</h1>
		<p>Hello {{$mailData['name'] }} ! Thank you for registering with Time to Do. We're excited to have you as a member of our community!</p>
		<p>If you didn't create an account with us, please ignore this email.</p>
		<img src="{{ $message->embed(public_path('logo.png')) }}" alt="LogoTime2Do" class="logo">
			</td>
		</tr>
	</table>
</body>
</html>
