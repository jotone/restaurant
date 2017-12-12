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
	$arr = [
		'15M 7 on 1 Comp StOmP No AiR NO ZeRG RUSH!!1!" — %username%',
		'An nescis, mi fili, quantilla sapientia mundus regatur?',
		'Who in the world has Chinese food for breakfast? The Chinese?',
		' %username%, %username%&hellip;',
		'*** STOP (0x0000000A, 0x0000000A, 0x0000000A, 0x0000000A) IRQ_NOT_LESS_OR_EQUAL, %username%.',
		'Artichokes play football. Purple monkeys dance invisibly.',
		'Bite my shiny metal ass, %username%.',
		'Cпасибо, что зашли, %username%.',
		'Don\'t panic, %username%!',
		'F5, %username%.',
		'If you got cojones, come on, mette mano.',
		'Jah’d never let us down, %username%.',
		'Solutions are not the answer, %username%.',
		'Tetris is so unrealistic, %username%',
		'You found piles and piles of gold! Keep Clicking! Click like the wind!',
		'You have completed more work units than 89,795% of our users, %username%.',
		'А ты знаешь, в чем соль, %username%?',
		'Береги колени, %username%',
		'Ваш горизонт завален, %username%.',
		'Все что вы сдесь видете, далжно оставатся сикретам, %username%.',
		'Вы не дождетесь новых приветствий, %username%.',
		'Вы не ошиблись дверью, %username%?',
		'Вы тоже имеете право на личную жизнь, %username%.',
		'Глаз страуса больше, чем его мозг, %username%.',
		'Деларова Евгения Евгеньевна нашлась, %username%!',
		'Если руки золотые, то не важно откуда они растут, %username%.',
		'Затоптать муравья не так просто, как кажется, %username%.',
		'Здравствуйте, %username%.',
		'Индейка — единственный зверь хитрее человека, %username%.',
		'Кто спит — тот видит только сны, %username%.',
		'Люди в тюрьме меньше времени сидят, чем вы на работе, %username%.',
		'Мне нужна твоя одежда и мотоцикл, %username%.',
		'Мы верим в ваc, %username%. Нет, правда.',
		'Надеюсь, приятно проводите время, %username%.',
		'Надо делать то, что нужно людям, а не то, чем мы занимаемся, %username%.',
		'Нет, %username%, учёные не такие дураки!',
		'Носороги не играют в игры, %username%.',
		'Ну и отлично, %username%!',
		'Он выпустил на свободу Разум биологов, %username%!',
		'Патрис Лумумба родился 2 июля, %username%!',
		'Паша Цветомузыка жив, %username%',
		'Передайте женщине соль, %username%.',
		'Подростки — не целевая аудитория табачных компаний, %username%.',
		'Попытка — первый шаг к провалу, %username%.',
		'По тебе плачет эстрада, %username%.',
		'Прислушивайтесь к голосам в вашей голове, %username%.',
		'Продолжайте кликать! Во имя всего святого, продолжайте кликать!',
		'Реквизит должен быть съеден, %username%',
		'Самоубийцы дискредитировали самоубийство, %username%.',
		'Сегодня вас ждёт приятная неожиданность, %username%!',
		'Слово «ЙА» — можно написать одной буквой, %username%.',
		'Ссылка, она для кого — наказание, а для кого — отдых, %username%.',
		'Тут никого нет, %username%! Никого! Никого, кроме тебя!',
		'У нас тут все либо умные, либо красивые, а вы хоть разорвитесь, %username%.',
		'Что общего у Майкла Джексона и Нила Армстронга? Лунная походка.',
		' %username% ненавидит детский сад',
	];
	$hello = str_replace('%username%',$user['name'],array_random($arr));
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
		<div>{{ $hello }}</div>
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