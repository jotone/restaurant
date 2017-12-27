@extends('admin.layouts.default')
@section('scripts')
	<script type="text/javascript" src="{{ asset('js/admin/scripts/visitors.js') }}"></script>
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
			<th>E-mail
				<div class="direction" id="email">
					<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=email&dir=asc') }}" class="asc fa fa-caret-up"></a>
					<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=email&dir=desc') }}" class="desc fa fa-caret-down"></a>
				</div>
			</th>
			<th>Имя
				<div class="direction" id="name">
					<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=name&dir=asc') }}" class="asc fa fa-caret-up"></a>
					<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=name&dir=desc') }}" class="desc fa fa-caret-down"></a>
				</div>
			</th>
			<th>Фамилия
				<div class="direction" id="surname">
					<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=surname&dir=asc') }}" class="asc fa fa-caret-up"></a>
					<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=surname&dir=desc') }}" class="desc fa fa-caret-down"></a>
				</div>
			</th>
			<th>Телефон
				<div class="direction" id="phone">
					<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=phone&dir=asc') }}" class="asc fa fa-caret-up"></a>
					<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=phone&dir=desc') }}" class="desc fa fa-caret-down"></a>
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
					<a class="view fa fa-eye" href="{{ route('admin.visitors.show', $item['id']) }}"></a>
				</td>
				<td>
					<a class="edit fa fa-pencil-square-o" href="{{ route('admin.visitors.edit', $item['id']) }}"></a>
				</td>
				<td>
					<a class="drop fa fa-times" data-id="{{ $item['id'] }}" data-title="{{ $item['email'] }}" href="#" ></a>
				</td>
				<td>{{ $item['id'] }}</td>
				<td>{{ $item['email'] }}</td>
				<td>{{ $item['name'] }}</td>
				<td>{{ $item['surname'] }}</td>
				<td>{{ $item['phone'] }}</td>
				<td>{{ $item['created'] }}</td>
				<td>{{ $item['updated'] }}</td>
			</tr>
		@endforeach
		</tbody>
	</table>
</div>
@stop