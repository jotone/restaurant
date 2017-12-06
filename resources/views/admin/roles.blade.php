@extends('admin.layouts.default')
@section('scripts')
	<script type="text/javascript" src="{{ asset('js/admin/scripts/roles.js') }}"></script>
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
			<a class="button" href="{{ route('admin.roles.create') }}">ADD</a>
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
				<th class="col_1_20">ID
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
				<th>Forbidden access to pages</th>
				<th>Users</th>
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
					@if($item['editable'] == 1)
						<a class="edit fa fa-pencil-square-o" href="{{ route('admin.roles.edit', $item['id']) }}"></a>
					@endif
				</td>
				<td>
					@if($item['editable'] == 1)
						<a class="drop fa fa-times" data-id="{{ $item['id'] }}" data-title="{{ $item['title'] }}" href="#" ></a>
					@endif
				</td>
				<td>{{ $item['id'] }}</td>
				<td>{{ $item['title'] }}</td>
				<td>
					@if(!is_array($item['pages']))
						{{ $item['pages'] }}
					@else
						<table class="items-list">
							<thead>
								<tr>
									<th>Page link</th>
									<th>Read</th>
									<th>Create</th>
									<th>Update</th>
									<th>Delete</th>
								</tr>
							</thead>
							<tbody>
							@foreach($item['pages'] as $inner_page)
								<tr>
									<td><a href="{{ asset($inner_page['slug']) }}">{{ $inner_page['title'] }}</a></td>
									<td>
										<?php
										$class = (strpos($inner_page['rules'],'r') !== false)? 'fa-ban': 'fa-check';
										?>
										<span class="fa {{$class}}"></span>
									</td>
									<td>
										<?php
										$class = (strpos($inner_page['rules'],'c') !== false)? 'fa-ban': 'fa-check';
										?>
										<span class="fa {{$class}}"></span>
									</td>
									<td>
										<?php
										$class = (strpos($inner_page['rules'],'u') !== false)? 'fa-ban': 'fa-check';
										?>
										<span class="fa {{$class}}"></span>
									</td>
									<td>
										<?php
										$class = (strpos($inner_page['rules'],'d') !== false)? 'fa-ban': 'fa-check';
										?>
										<span class="fa {{$class}}"></span>
									</td>
								</tr>
							@endforeach
							</tbody>
						</table>
					@endif
				</td>
				<td>
					@foreach($item['users'] as $user)
						<p><a href="{{ route('admin.users.show', $user['id']) }}">{{ $user['name'] }} ({{ $user['email'] }})</a></p>
					@endforeach
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