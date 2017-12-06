@extends('admin.layouts.default')
@section('scripts')
	<script type="text/javascript" src="{{ asset('js/admin/scripts/roles.js') }}"></script>
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

			<form	name="role"
					method="post"
					target="_self"
					action="@if(isset($content)){{ route('admin.roles.update', $content->id) }}@else{{ route('admin.roles.store') }}@endif">
				{{ csrf_field() }}
				@if(isset($content))
					{{ method_field('PUT') }}
				@endif
				<input name="id" type="hidden" value="@if(isset($content)){{ $content->id }}@endif">

				<fieldset>
					<legend>Main data</legend>
					<div class="row-wrap">
						<label>
							<input	name="title"
									class="input-text col_1_2"
									type="text"
									required="required"
									placeholder="Title&hellip;"
									value="@if(isset($content)){{ $content->title }}@endif">
							<span>Title</span>
						</label>
					</div>
				</fieldset>

				<fieldset>
					<legend>Forbidden pages</legend>
					<div class="row-wrap">
						<div class="chbox-selector-wrap">
							@foreach($access_pages as $page_id => $access_page)
								@if(!(
									(isset($forbidden_pages['_'.$page_id])) &&
									(strpos($forbidden_pages['_'.$page_id], 'r') !== false) &&
									(strpos($forbidden_pages['_'.$page_id], 'c') !== false) &&
									(strpos($forbidden_pages['_'.$page_id], 'u') !== false) &&
									(strpos($forbidden_pages['_'.$page_id], 'd') !== false)
								))
								<div class="chbox-selector-item">
									<label class="item-row">
										<div class="checkbox-wrap">
										<?php
										$checked = (
											(isset($content->access_pages->$page_id)) &&
											(strpos($content->access_pages->$page_id, 'r') !== false) &&
											(strpos($content->access_pages->$page_id, 'c') !== false) &&
											(strpos($content->access_pages->$page_id, 'u') !== false) &&
											(strpos($content->access_pages->$page_id, 'd') !== false)
										)? 'checked="checked"' : '';
										?>
											<input class="crud-control" type="checkbox" value="{{ $page_id }}" {{ $checked }}>
										</div>
										<div>
											<span class="fa {{ $access_page['img'] }}"></span><span>{{ $access_page['title'] }}</span>
										</div>
									</label>
									<div class="item-row">
										<div class="checkbox-wrap"></div>
										<div><a href="{{ asset($access_page['slug']) }}">{{ $access_page['slug'] }}</a></div>
									</div>
									<div class="item-row">
										<div class="checkbox-wrap"></div>
										<div class="crud-wrap">Deny actions:</div>
									</div>
									<div class="item-row">
										<div class="checkbox-wrap"></div>
										<div class="crud-wrap">
											@if(!(
												(isset($forbidden_pages['_'.$page_id])) &&
												(strpos($forbidden_pages['_'.$page_id], 'r') !== false)
											))
											<label class="col_1_2">
												<?php
												$checked = (
													(isset($content->access_pages->$page_id)) &&
													(strpos($content->access_pages->$page_id, 'r') !== false)
												)? 'checked="checked"' : '';
												?>
												<input name="read[]" class="crud-input" type="checkbox" value="{{ $page_id }}" {{ $checked }}>
												<span>read</span>
											</label>
											@endif

											@if(!(
												(isset($forbidden_pages['_'.$page_id])) &&
												(strpos($forbidden_pages['_'.$page_id], 'c') !== false)
											))
											<label class="col_1_2">
												<?php
												$checked = (
													(isset($content->access_pages->$page_id)) &&
													(strpos($content->access_pages->$page_id, 'c') !== false)
												)? 'checked="checked"' : '';
												?>
												<input name="create[]" class="crud-input" type="checkbox"value="{{ $page_id }}" {{ $checked }}>
												<span>create</span>
											</label>
											@endif

											@if(!(
												(isset($forbidden_pages['_'.$page_id])) &&
												(strpos($forbidden_pages['_'.$page_id], 'u') !== false)
											))
											<label class="col_1_2">
												<?php
												$checked = (
													(isset($content->access_pages->$page_id)) &&
													(strpos($content->access_pages->$page_id, 'u') !== false)
												)? 'checked="checked"' : '';
												?>
												<input name="update[]" class="crud-input" type="checkbox"value="{{ $page_id }}" {{ $checked }}>
												<span>update</span>
											</label>
											@endif

											@if(!(
												(isset($forbidden_pages['_'.$page_id])) &&
												(strpos($forbidden_pages['_'.$page_id], 'd') !== false)
											))
											<label class="col_1_2">
												<?php
												$checked = (
													(isset($content->access_pages->$page_id)) &&
													(strpos($content->access_pages->$page_id, 'd') !== false)
												)? 'checked="checked"' : '';
												?>
												<input name="delete[]" class="crud-input" type="checkbox"value="{{ $page_id }}" {{ $checked }}>
												<span>delete</span>
											</label>
											@endif
										</div>
									</div>
								</div>
								@endif
							@endforeach
						</div>
					</div>
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
					<button name="save" class="button" type="submit">Save</button>
				</div>
			</form>
		</div>
	</div>
</div>
@stop