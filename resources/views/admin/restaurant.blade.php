@extends('admin.layouts.default')
@section('scripts')
	<script type="text/javascript" src="{{ asset('js/admin/scripts/restaurant.js') }}"></script>
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
				<a class="button" href="{{ route('admin.restaurant.create') }}">Добавить</a>
			</div>
			<div class="pagination-wrap">
				@if(1 < $pagination['last_page'])
					@include('admin.layouts.pagination')
				@endif
			</div>
		</div>

		<table class="items-list">
			<thead>
			<tr>
				<th class="col_1_20"></th>
				<th class="col_1_20"></th>
				<th class="col_1_20"></th>
				<th class="col_1_20">ID
					<div class="direction" id="id">
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=id&dir=asc') }}" class="asc fa fa-caret-up"></a>
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=id&dir=desc') }}" class="desc fa fa-caret-down"></a>
					</div>
				</th>
				<th>Название
					<div class="direction" id="title">
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=title&dir=asc') }}" class="asc fa fa-caret-up"></a>
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=title&dir=desc') }}" class="desc fa fa-caret-down"></a>
					</div>
				</th>
				<th>Ссылка
					<div class="direction" id="slug">
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=slug&dir=asc') }}" class="asc fa fa-caret-up"></a>
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=slug&dir=desc') }}" class="desc fa fa-caret-down"></a>
					</div>
				</th>
				<th>Лого</th>
				<th>Адрес
					<div class="direction" id="address">
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=address&dir=asc') }}" class="asc fa fa-caret-up"></a>
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=address&dir=desc') }}" class="desc fa fa-caret-down"></a>
					</div>
				</th>
				<th>Список меню</th>
				<th>Рейтинг
					<div class="direction" id="rating">
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=rating&dir=asc') }}" class="asc fa fa-caret-up"></a>
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=rating&dir=desc') }}" class="desc fa fa-caret-down"></a>
					</div>
				</th>
				<th>Статус
					<div class="direction" id="enabled">
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=enabled&dir=asc') }}" class="asc fa fa-caret-up"></a>
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=enabled&dir=desc') }}" class="desc fa fa-caret-down"></a>
					</div>
				</th>
				<th>Просмотры
					<div class="direction" id="views">
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=views&dir=asc') }}" class="asc fa fa-caret-up"></a>
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=views&dir=desc') }}" class="desc fa fa-caret-down"></a>
					</div>
				</th>
				<th>Создан
					<div class="direction" id="created_at">
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=created_at&dir=asc') }}" class="asc fa fa-caret-up"></a>
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=created_at&dir=desc') }}" class="desc fa fa-caret-down"></a>
					</div>
				</th>
				<th>Изменен
					<div class="direction" id="updated_at">
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=updated_at&dir=asc') }}" class="asc fa fa-caret-up"></a>
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=updated_at&dir=desc') }}" class="desc fa fa-caret-down"></a>
					</div>
				</th>
			</tr>
			</thead>
			<tbody>
			@foreach($content as $item)
				<tr>
					<td>
						<a class="view fa fa-eye" href="{{ route('admin.restaurant.show', $item['id']) }}"></a>
					</td>
					<td>
						<a class="edit fa fa-pencil-square-o" href="{{ route('admin.restaurant.edit', $item['id']) }}"></a>
					</td>
					<td>
						<a class="drop fa fa-times" data-id="{{ $item['id'] }}" data-title="{{ $item['title'] }}" href="#" ></a>
					</td>
					<td>{{ $item['id'] }}</td>
					<td>{{ $item['title'] }}</td>
					<td>{{ $item['slug'] }}</td>
					<td>
						@if(!empty($item['logo']))
							<img src="{{ asset($item['logo']->src) }}" alt="">
						@endif
					</td>
					<td>{{ $item['address'] }}</td>
					<td>
						@foreach($item['menus'] as $menu)
							<p><a href="{{ route('admin.menu.show', $menu['id']) }}">{{ $menu['title'] }}</a></p>
						@endforeach
					</td>
					<td>
						@if(!empty($item['rating']))
							<p><span class="fa fa-thumbs-o-up">&nbsp;+&nbsp;{{ $item['rating']['p'] }}</span></p>
							<p><span class="fa fa-thumbs-o-down">&nbsp;&minus;&nbsp;{{ $item['rating']['n'] }}</span></p>
						@endif
					</td>
					<td>
						@if($item['enabled'] == 1)
							<span class="fa fa-check"></span>
							Включено
						@else
							<span class="fa fa-ban"></span>
							Выключено
						@endif
					</td>
					<td>{{ $item['views'] }}</td>
					<td>
						<p>{{ $item['created'] }}</p>
						@if(!empty($item['created_by']))
							<p>{{ $item['created_by']['name'] }}</p>
							<p>{{ $item['created_by']['email'] }}</p>
						@endif
					</td>
					<td>
						<p>{{ $item['updated'] }}</p>
						@if(!empty($item['updated_by']))
							<p>{{ $item['updated_by']['name'] }}</p>
							<p>{{ $item['updated_by']['email'] }}</p>
						@endif
					</td>
				</tr>
			@endforeach
			</tbody>
		</table>
	</div>
@stop