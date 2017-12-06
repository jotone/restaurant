@extends('admin.layouts.default')
@section('scripts')
	<script type="text/javascript" src="{{ asset('js/admin/scripts/category_types.js') }}"></script>
@stop
@section('content')
<div class="main-wrap">
	<div class="edition-wrap">
		<aside class="goto-block"><ul></ul></aside>

		<div class="workplace-wrap border">
			<div class="page-info">
				<div class="page-title">{{ $title }}</div>
				<div class="breadcrumbs-wrap">
					@include('admin.layouts.breadcrumbs')
				</div>
			</div>

			<form	name="category_type"
					method="post"
					target="_self"
					action="@if(isset($content->id)){{ route('admin.category_types.update',$content->id) }}@else{{ route('admin.category_types.store') }}@endif">
				{{ csrf_field() }}
				@if(isset($content))
					{{ method_field('PUT') }}
				@endif
				<input name="id" type="hidden" value="@if(isset($content->id)){{ $content->id }}@endif">
				<fieldset>
					<legend>Main data</legend>
					<div class="row-wrap">
						<label>
						@if(!isset($content) || isset($content->id))
							<input	name="title"
									class="input-text col_1_2"
									type="text"
									required="required"
									placeholder="Title&hellip;"
									value="@if(isset($content)){{ $content->title }}@endif">
							<span>Title</span>
						@else
							<span>Title: {{ $content->title }}</span>
						@endif
						</label>
					</div>
					<div class="row-wrap">
						<label>
						@if(!isset($content) || isset($content->id))
							<input	name="slug"
									class="input-text col_1_2"
									type="text"
									placeholder="Link&hellip;"
									value="@if(isset($content)){{ $content->slug }}@endif">
							<span>Link</span>
						@else
							<span>Link: {{ $content->slug }}</span>
						@endif
						</label>
					</div>
					<div class="row-wrap">
						<label>
							<input	name="enabled"
									class="chbox-input"
									type="checkbox"
									@if(isset($content) && ($content->enabled == 1)) checked="checked" @endif
									@if(isset($content) && !isset($content->id)) disabled="disabled" @endif>
							<span>Enabled</span>
						</label>
					</div>
				</fieldset>

				<fieldset>
					<legend>Options</legend>
					@foreach($options as $caption => $option)
						<div class="row-wrap">
							<label>
								<input	name="option_{{ $caption }}"
										class="chbox-input"
										type="checkbox"
										@if(isset($content) && ($content->options->$caption == 1)) checked="checked" @endif
										@if(isset($content) && !isset($content->id)) disabled="disabled" @endif>
								<span>{{ $option }}</span>
							</label>
						</div>
					@endforeach
				</fieldset>

				<fieldset>
					<legend>Categories List</legend>
					<div class="categories-list-wrap">@if(isset($categories)){!! $categories !!}@endif</div>
					@if(isset($content->id))
						<div class="button-wrap">
							<a class="button" href="@if(isset($content->id)){{ route('admin.category.create', $content->id) }}@endif">Add Category</a>
						</div>
					@endif
				</fieldset>

				@if(isset($content))
					<div class="details">
						<p>Created by:
							@if(!empty($content->created_by) && !is_numeric($content->created_by))
								<em>{{ $content->created_by['name'] }} (<ins>{{ $content->created_by['email'] }}</ins>) {{ $content->created_at }}</em>
							@else
								Unknown
							@endif
						</p>
						<p>Updated by:
							@if(!empty($content->updated_by) && !is_numeric($content->updated_by))
								<em>{{ $content->updated_by['name'] }} (<ins>{{ $content->updated_by['email'] }}</ins>) {{ $content->updated_at }}</em>
							@else
								Unknown
							@endif
						</p>
					</div>
				@endif

				<div class="form-button-wrap">
					@if(!isset($content) || isset($content->id))
						<button name="save" class="button" type="submit">Save</button>
					@endif
				</div>
			</form>
		</div>
	</div>
</div>
@stop