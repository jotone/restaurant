@extends('admin.layouts.default')
@section('scripts')
	<script type="text/javascript" src="{{ asset('js/admin/scripts/meal_menu.js') }}"></script>
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
				<a class="button" href="{{ route('admin.menu.create') }}">Добавить</a>
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
				<th></th>
				<th></th>
				<th></th>
				<th>ID
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
				<th>Ресторан
					<div class="direction" id="restaurant_id">
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=restaurant_id&dir=asc') }}" class="asc fa fa-caret-up"></a>
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=restaurant_id&dir=desc') }}" class="desc fa fa-caret-down"></a>
					</div>
				</th>
				<th>Блюда</th>
				<th>Статус
					<div class="direction" id="enabled">
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=enabled&dir=asc') }}" class="asc fa fa-caret-up"></a>
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=enabled&dir=desc') }}" class="desc fa fa-caret-down"></a>
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
						<a class="view fa fa-eye" href="{{ route('admin.menu.show', $item['id']) }}"></a>
					</td>
					<td>
						<a class="edit fa fa-pencil-square-o" href="{{ route('admin.menu.edit', $item['id']) }}"></a>
					</td>
					<td>
						<a class="drop fa fa-times" data-id="{{ $item['id'] }}" data-title="{{ $item['title'] }}" href="#" ></a>
					</td>
					<td>{{ $item['id'] }}</td>
					<td>{{ $item['title'] }}</td>
					<td>
						@if(!empty($item['restaurant']))
							@foreach($item['restaurant'] as $id => $title)
								<p>Ресторан: <a href="{{ route('admin.restaurant.show', $id) }}">{{ $title }}</a></p>
							@endforeach
						@endif
					</td>
					<td>
						@foreach($item['dishes'] as $id => $title)
							<p><a href="{{ route('admin.dish.show', $id) }}">{{ $title }}</a></p>
						@endforeach
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