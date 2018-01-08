<?php
$time = microtime();
$time = explode(' ', $time);
$total_time = round(($time[1] + $time[0] - $start),4);
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="description" content="" />
	<meta name="keywords" content="" />
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Admin</title>

	<link href="https://fonts.googleapis.com/css?family=Ubuntu" rel="stylesheet">
	<link rel="stylesheet" href="{{ asset('css/reset.css') }}">
	<link rel="stylesheet" href="{{ asset('css/short-validation.css') }}">
	<link rel="stylesheet" href="{{ asset('css/font-awesome.css') }}">
	<link rel="stylesheet" href="{{ asset('css/air-datepicker.css') }}">
	<link rel="stylesheet" href="{{ asset('css/admin.css') }}">

	<script type="text/javascript" src="{{ asset('js/jquery.3.2.1.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/jquery.mask.js') }}"></script>

	<script type="text/javascript" src="{{ asset('js/admin/build-config.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/admin/ckeditor.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/admin/config.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/admin/styles.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/admin/air-datepicker.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/admin/debounce.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/admin/short-validation.js') }}"></script>
</head>
<body>
<header>
	<?php
	$user = Auth::user();
	?>
	<nav class="top-menu">
		<div class="nav-icon-menu fa-bars"></div>
		{!! \App\Http\Controllers\AppController::topMenu($page) !!}
		<div class="user-menu">
			<div><a href="{{ route('logout') }}"><span class="fa fa-sign-out"></span>&nbsp;Выйти</a></div>
		</div>
	</nav>
</header>
@yield('content')
<footer>
	<div class="footer-wrap">
		<div></div>
		<div>Страница создана за {{ $total_time }} секунд</div>

		<div class="status-bar" @if(!empty($errors->all())) style="display: block;" @endif>
			<div class="status-messages-wrap">
				<div class="close-status-bar fa fa-times-circle"></div>
				<div class="messages">
					@if(!empty($errors->all()))
						@foreach($errors->all() as $error)
							<p class="error">{{ $error }}</p>
						@endforeach
					@endif
				</div>
			</div>
		</div>

	</div>
</footer>

<div class="error-popup">
	<div class="close-popup fa fa-times"></div>
	<div class="popup-caption"><span></span></div>
	<div class="error-wrap">
		<div class="error-body"></div>
	</div>
</div>

<div class="confirm-popup">
	<div class="confirm-message-wrap"></div>
	<div class="confirm-controls-wrap">
		<div>
			<button name="yes" class="button" type="button">OK</button>
		</div>
		<div>
			<button name="no" class="button" type="button">Отмена</button>
		</div>
	</div>
</div>

<div class="overlay-popup"></div>

<div class="overview-popup">
	<div class="close-popup fa fa-times"></div>
	<div class="popup-images"></div>
	<div class="form-button-wrap">
		<button name="addImageFromSaved" class="button" type="button">Применить</button>
	</div>
</div>


<div class="custom-slider-preview-popup">
	<div class="close-popup fa fa-times"></div>
	<div class="custom-slider-preview-wrap">
		<ul></ul>
	</div>
</div>
<script type="text/javascript" src="{{ asset('js/admin/scripts/scn.js') }}"></script>
@yield('scripts')
</body>
</html>