<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="description" content="" />
	<meta name="keywords" content="" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Admin</title>

	<link href="https://fonts.googleapis.com/css?family=Ubuntu" rel="stylesheet">
	<link rel="stylesheet" href="{{ asset('css/reset.css') }}">
	<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>

<div class="col-1">
	<div class="login-wrap">
		<form action="{{ route('admin.login') }}" method="POST" target="_self">
			{{ csrf_field() }}
			<div>
				<input name="email" class="form-field" type="email" required="required" placeholder="Login&hellip;">
			</div>
			<div>
				<input name="password" class="form-field" type="password" required="required" placeholder="Password&hellip;">
			</div>
			<div>
				<button name="submit" class="action-button" style="margin: 0 auto" type="submit">Enter</button>
			</div>
		</form>
	</div>
</div>

</body>
</html>