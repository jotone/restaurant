@extends('admin.layouts.default')
@section('scripts')
	<script type="text/javascript" src="{{ asset('js/admin/scripts/gallery.js') }}"></script>
@stop
@section('content')
<div class="main-wrap">
	<div class="page-info">
		<div class="page-title">{{ $title }}</div>
		<div class="breadcrumbs-wrap">
			@include('admin.layouts.breadcrumbs')
		</div>
	</div>

	<div class="items-controls-wrap">
		<div class="button-wrap">
			<noscript>
			<form name="galleryUpload" action="{{ route('admin.gallery.create') }}" method="POST" target="_self" enctype="multipart/form-data">
				{{ csrf_field() }}
				<input name="upload[]" type="file" multiple="multiple">
				<button name="send" class="button inline" type="submit">Загрузить файлы</button>
			</form>
			</noscript>
		</div>
		<div class="pagination-wrap">

		</div>
	</div>

	<div class="images-wrap">
		@foreach($content as $item)
			<div class="image-container">
				<div class="image-wrap @if(empty($item['used_in'])){{ 'active' }}@endif">
					<div class="drop-image">
						<span class="fa fa-times"></span>
					</div>
					<img src="{{ asset($item['src']) }}" alt="">
				</div>
				<div class="image-info">
					<p data-type="name">Файл:
						<?php
						$name = explode('/',$item['src']);
						$name = $name[count($name)-1];
						?>
						<span>{{ $name }}</span>
					</p>
					<p>Размер: {{ \App\Http\Controllers\AppController::niceFilesize($item['src']) }}</p>
					@if(!empty($item['used_in']))
						<p>Используется для:</p>
						<ul class="link-list-wrap">
						@foreach($item['used_in'] as $type => $used_in)
							<li>
								<?php
								switch($type){
									case 'category': echo 'Категории:'; break;
									case 'restaurant': echo 'Ресторан:'; break;
									case 'dish': echo 'Блюда:'; break;
									case 'pages': echo 'Страницы:'; break;
								}
								?>
								<ul>
								@foreach($used_in as $id => $title)
									<li><a href="{{ route('admin.'.$type.'.edit', $id) }}">{{ $title }}</a></li>
								@endforeach
								</ul>
							</li>
						@endforeach
						</ul>
					@else
						<p>Нигде не используется</p>
					@endif
				</div>
			</div>
		@endforeach
	</div>
</div>
@stop