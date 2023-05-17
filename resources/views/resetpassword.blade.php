<!DOCTYPE html>
<html>
<head>
	<title>Reset Password Email</title>
	<style>
		body {
			font-family: "Lucida Sans", "Lucida Sans Regular", "Lucida Grande", "Lucida Sans Unicode", Geneva, Verdana, sans-serif;
			text-align: center;
			display : flex;
			align-items: center;
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
		<h1>Reset Your Password</h1>
	<p>We received a request to reset your password. To reset your password, click the button below:</p>
	<p><a href="#" class="button">Reset Password</a></p>
	<p>If you didn't make this request, you can safely ignore this email and your password will not be changed.</p>
	<img src="{{ $message->embed(public_path('logo.png')) }}" alt="LogoTime2Do" class="logo">
        </td>
    </tr>
</table>
	<!--<h1>Reset Your Password</h1>
	<p>We received a request to reset your password. To reset your password, click the button below:</p>
	<p><a href="#" class="button">Reset Password</a></p>
	<p>If you didn't make this request, you can safely ignore this email and your password will not be changed.</p>
	<img src="{{ $message->embed(public_path('logo.png')) }}" alt="LogoTime2Do" class="logo">-->
</body>
</html>
