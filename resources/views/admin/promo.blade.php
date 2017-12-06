@extends('admin.layouts.default')
@section('scripts')
	<script type="text/javascript" src="{{ asset('js/admin/scripts/promo.js') }}"></script>
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
			<a class="button" href="{{ route('admin.promo.create') }}">ADD</a>
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
				<th>Title
					<div class="direction" id="title">
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=title&dir=asc') }}" class="asc fa fa-caret-up"></a>
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=title&dir=desc') }}" class="desc fa fa-caret-down"></a>
					</div>
				</th>
				<th>Link
					<div class="direction" id="slug">
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=slug&dir=asc') }}" class="asc fa fa-caret-up"></a>
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=slug&dir=desc') }}" class="desc fa fa-caret-down"></a>
					</div>
				</th>
				<th>Preview Image</th>
				<th>Start Date
					<div class="direction" id="date_start">
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=date_start&dir=asc') }}" class="asc fa fa-caret-up"></a>
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=date_start&dir=desc') }}" class="desc fa fa-caret-down"></a>
					</div>
				</th>
				<th>Finish Date
					<div class="direction" id="date_finish">
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=date_finish&dir=asc') }}" class="asc fa fa-caret-up"></a>
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=date_finish&dir=desc') }}" class="desc fa fa-caret-down"></a>
					</div>
				</th>
				<th>Discount</th>
				<th>Products</th>
				<th>Views
					<div class="direction" id="views">
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=views&dir=asc') }}" class="asc fa fa-caret-up"></a>
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=views&dir=desc') }}" class="desc fa fa-caret-down"></a>
					</div>
				</th>
				<th>Published at
					<div class="direction" id="published_at">
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=published_at&dir=asc') }}" class="asc fa fa-caret-up"></a>
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=published_at&dir=desc') }}" class="desc fa fa-caret-down"></a>
					</div>
				</th>
				<th>Created at
					<div class="direction" id="created_at">
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=created_at&dir=asc') }}" class="asc fa fa-caret-up"></a>
						<a href="{{ asset($page.'/?page='.$pagination['current_page'].'&sort_by=created_at&dir=desc') }}" class="desc fa fa-caret-down"></a>
					</div>
				</th>
				<th>Updated at
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
					<a class="view fa fa-eye" href="{{ route('admin.promo.show', $item['id']) }}"></a>
				</td>
				<td>
					<a class="edit fa fa-pencil-square-o" href="{{ route('admin.promo.edit', $item['id']) }}"></a>
				</td>
				<td>
					<a class="drop fa fa-times" data-id="{{ $item['id'] }}" data-title="{{ $item['title'] }}" href="#" ></a>
				</td>
				<td>{{ $item['id'] }}</td>
				<td>{{ $item['title'] }}</td>
				<td>{{ $item['slug'] }}</td>
				<td>
				@if(!empty($item['img_url']))
					<img src="{{ asset($item['img_url']->src) }}" alt="">
				@endif
				</td>
				<td>{{ $item['start'] }}</td>
				<td>{{ $item['finish'] }}</td>
				<td>{{ $item['discount'] }}</td>
				<td>
				@foreach($item['products'] as $product)
					<p><a href="{{ route('admin.products.show', $product->id) }}">{{ $product->title }}</a></p>
				@endforeach
				</td>
				<td>{{ $item['views'] }}</td>
				<td>{{ $item['published'] }}</td>
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