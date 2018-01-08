@extends('admin.layouts.default')
@section('scripts')
	<script type="text/javascript" src="{{ asset('js/admin/scripts/home.js') }}"></script>
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
			<th class="col_1_20">ID
				<div class="direction" id="id">
					<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=id&dir=asc') }}" class="asc fa fa-caret-up"></a>
					<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=id&dir=desc') }}" class="desc fa fa-caret-down"></a>
				</div>
			</th>
			<th>Имя
				<div class="direction" id="name">
					<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=name&dir=asc') }}" class="asc fa fa-caret-up"></a>
					<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=name&dir=desc') }}" class="desc fa fa-caret-down"></a>
				</div>
			</th>
			<th>Ресторан
				<div class="direction" id="restaurant">
					<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=restaurant&dir=asc') }}" class="asc fa fa-caret-up"></a>
					<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=restaurant&dir=desc') }}" class="desc fa fa-caret-down"></a>
				</div>
			</th>
			<th>Блюда</th>
			<th>Дата заказа
				<div class="direction" id="created_at">
					<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=created_at&dir=asc') }}" class="asc fa fa-caret-up"></a>
					<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=created_at&dir=desc') }}" class="desc fa fa-caret-down"></a>
				</div>
			</th>
		</tr>
		</thead>
		<tbody>
		@foreach($content as $item)
			<tr>
				<td>
					<a class="drop fa fa-times" data-id="{{ $item['id'] }}" href="#" ></a>
				</td>
				<td>{{ $item['id'] }}</td>
				<td>{{ $item['visitor'] }}</td>
				<td>{{ $item['restaurant'] }}</td>
				<td>
					<table class="col_1">
						<thead>
							<tr>
								<th>Блюдо</th>
								<th>Цена</th>
								<th>Кол-во</th>
							</tr>
						</thead>
						<tbody>
							<?php $total_price = 0; ?>
							@foreach($item['items'] as $dish)
							<tr>
								<td>{{ $dish['title'] }}</td>
								<td>{{ $dish['price'] }}</td>
								<td>{{ $dish['quantity'] }}</td>
							</tr>
							<?php $total_price += $dish['price']; ?>
							@endforeach
							<tr style="border-top: 1px solid #000">
								<td><strong>Итого</strong></td>
								<td>{{ $total_price }}</td>
								<td></td>
							</tr>
						</tbody>
					</table>
				</td>
				<td>{{ $item['created'] }}</td>
			</tr>
		@endforeach
		</tbody>
	</table>
</div>
@stop