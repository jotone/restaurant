@extends('admin.layouts.default')
@section('scripts')
	<script type="text/javascript" src="{{ asset('js/admin/scripts/pages.js') }}"></script>
@stop
@section('content')
<div class="main-wrap">
	<div class="edition-wrap">
		<aside class="goto-block">
			@if(!empty($templates))
				<form name="changeTemplate" method="GET" target="_self" action="{{ route('admin.pages.create') }}">
				<select name="template" class="input-text" style="margin-bottom: 10px; min-width: 200px;">
					<option value="0">No template</option>
					@foreach($templates as $template)
						<option value="{{ $template->id }}" @if(!empty($current_template->id) && ($template->id == $current_template->id))selected="selected"@endif>
							{{ $template->title }}
						</option>
					@endforeach
				</select>
				<noscript>
					<button name="apply" type="submit" class="button">Apply</button>
				</noscript>
				</form>
			@endif
			<ul></ul>
		</aside>

		<div class="workplace-wrap border">
			<div class="page-info">
				<div class="page-title">{{ $title }}</div>
				<div class="breadcrumbs-wrap">
					@include('admin.layouts.breadcrumbs')
				</div>
			</div>

			<form	name="pages"
					method="post"
					target="_self"
					action="@if(isset($content->id)){{ route('admin.pages.update', $content->id) }}@else{{ route('admin.pages.store') }}@endif"
					enctype="multipart/form-data">
				{{ csrf_field() }}
				@if(isset($content))
					{{ method_field('PUT') }}
				@endif
				<input name="id" type="hidden" value="@if(isset($content->id)){{ $content->id }}@endif">
				<?php if(isset($type) && ($type == 0)){unset($content->id);} ?>
				<input name="template_id" type="hidden" value="@if(!empty($current_template->id)){{ $current_template->id }}@else{{'0'}}@endif">

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
							<input name="enabled" class="chbox-input" type="checkbox" @if(isset($content) && ($content->enabled == 1)) checked="checked" @endif @if(isset($content) && !isset($content->id)) disabled="disabled" @endif>
							<span>Enabled</span>
						</label>
					</div>
				</fieldset>

				<div id="htmlContent">
				@if(!empty($current_template))
					{!! $current_template->html_content !!}
				@endif
				</div>

				<fieldset>
					<legend>Meta Data</legend>
					<div class="row-wrap">
						<label>
							<input	name="meta_title"
									class="input-text col_1_2"
									type="text"
									placeholder="Meta Title&hellip;"
									value="@if(isset($content)){{ $content->meta_title }}@endif">
							<span>Meta Title</span>
						</label>
					</div>
					<div class="row-wrap">
						<p>Meta Description</p>
						<textarea name="meta_description" class="text-area-middle">@if(isset($content)){{ $content->meta_description }}@endif</textarea>
					</div>
					<div class="row-wrap">
						<p>Meta Keywords</p>
						<textarea name="meta_keywords" class="text-area-middle">@if(isset($content)){{ $content->meta_keywords }}@endif</textarea>
					</div>
				</fieldset>

				<fieldset>
					<legend>SEO data</legend>
					<div class="row-wrap">
						<label>
							<input	name="need_seo"
									class="chbox-input"
									type="checkbox"
									@if(isset($content) && ($content->need_seo == 1)) checked="checked" @endif>
							<span>Allow seo for this category</span>
						</label>
					</div>
					<div class="row-wrap">
						<label>
							<input	name="seo_title"
									class="input-text col_1_2"
									type="text"
									placeholder="SEO Title&hellip;"
									value="@if(isset($content)){{ $content->seo_title }}@endif">
							<span>SEO Title</span>
						</label>
					</div>
					<div class="row-wrap">
						<p>SEO text</p>
						<textarea name="seo_text" class="text-area needCKE">@if(isset($content)){{ $content->seo_text }}@endif</textarea>
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
					@if(!isset($content) || isset($content->id))
						<button name="save" class="button" type="submit">Save</button>
					@endif
				</div>
			</form>
		</div>
	</div>
</div>
@stop