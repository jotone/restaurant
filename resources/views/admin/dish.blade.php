@extends('admin.layouts.default')
@section('scripts')
	<script type="text/javascript" src="{{ asset('js/admin/scripts/dish.js') }}"></script>
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
				<a class="button" href="{{ route('admin.dish.create') }}">Добавить</a>
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
				<th>Изображение</th>
				<th>Категория</th>
				<th>Цена
					<div class="direction" id="price">
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=price&dir=asc') }}" class="asc fa fa-caret-up"></a>
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=price&dir=desc') }}" class="desc fa fa-caret-down"></a>
					</div>
				</th>
				<th>Текст</th>
				<th>Входит в меню</th>
				<th>Рекомендовано
					<div class="direction" id="is_recommended">
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=is_recommended&dir=asc') }}" class="asc fa fa-caret-up"></a>
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=is_recommended&dir=desc') }}" class="desc fa fa-caret-down"></a>
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
						<a class="view fa fa-eye" href="{{ route('admin.dish.show', $item['id']) }}"></a>
					</td>
					<td>
						<a class="edit fa fa-pencil-square-o" href="{{ route('admin.dish.edit', $item['id']) }}"></a>
					</td>
					<td>
						<a class="drop fa fa-times" data-id="{{ $item['id'] }}" data-title="{{ $item['title'] }}" href="#" ></a>
					</td>
					<td>{{ $item['id'] }}</td>
					<td>{{ $item['title'] }}</td>
					<td>
						@if(!empty($item['img_url']))
							<img src="{{ asset($item['img_url']['src']) }}" alt="">
						@endif
					</td>
					<td>
						@if(!empty($item['categories']))
							@foreach($item['categories'] as $category)
								@if($category['id'] != 0)
									<a href="{{ route('admin.category.edit', $category['id']) }}">{{ $category['title'] }}</a>
								@else
									<p>{{ $category['title'] }}</p>
								@endif
							@endforeach
						@endif
					</td>
					<td>
						@if(!empty($item['price']))
							{{ number_format($item['price'], 2, ',', ' ') }}
						@else
							0
						@endif
					</td>
					<td>{{ $item['text'] }}</td>
					<td>
						@foreach($item['menus'] as $menu)
							<p>
								Ресторан: <a href="{{ route('admin.restaurant.show', $menu['restaurant']['id']) }}">
									{{ $menu['restaurant']['title'] }}
								</a><br>
								Меню: <a href="{{ route('admin.menu.show', $menu['id']) }}">
									{{ $menu['title'] }}
								</a>
							</p><br>
						@endforeach
					</td>
					<td>
						@if($item['is_recommended'] == 1)
							<span class="fa fa-check"></span>
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