@extends('admin.layouts.default')
@section('scripts')
	<script type="text/javascript" src="{{ asset('js/admin/scripts/comments.js') }}"></script>
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
				<th>Пользователь
					<div class="direction" id="user_id">
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=user_id&dir=asc') }}" class="asc fa fa-caret-up"></a>
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=user_id&dir=desc') }}" class="desc fa fa-caret-down"></a>
					</div>
				</th>
				<th>Текст</th>
				<th>Ресторан
					<div class="direction" id="post_id">
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=post_id&dir=asc') }}" class="asc fa fa-caret-up"></a>
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=post_id&dir=desc') }}" class="desc fa fa-caret-down"></a>
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
					<a class="view fa fa-eye" href="{{ route('admin.comments.show', $item['id']) }}"></a>
				</td>
				<td>
					<a class="edit fa fa-pencil-square-o" href="{{ route('admin.comments.edit', $item['id']) }}"></a>
				</td>
				<td>
					<a class="drop fa fa-times" data-id="{{ $item['id'] }}" href="#" ></a>
				</td>
				<td>{{ $item['id'] }}</td>
				<td>
					<a href="{{ route('admin.users.edit', $item['user']['id']) }}">
						{{ $item['user']['name'] }} ({{ $item['user']['email'] }})
					</a>
				</td>
				<td>{{ $item['text'] }}</td>
				<td>
					@if(isset($item['post']['id']))
					<a href="{{ route('admin.restaurant.edit', $item['post']['id']) }}">
						{{ $item['post']['title'] }}
					</a>
					@endif
				</td>
				<td>{{ $item['created'] }}</td>
				<td>{{ $item['updated'] }}</td>
			</tr>
		@endforeach
		</tbody>
	</table>
</div>
@stop