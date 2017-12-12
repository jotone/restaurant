@extends('admin.layouts.default')
@section('scripts')
	<script type="text/javascript" src="{{ asset('js/admin/scripts/templates.js') }}"></script>
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

			<form	name="templates"
					method="post"
					target="_self"
					action="@if(isset($content->id)){{ route('admin.templates.update', $content->id) }}@else{{ route('admin.templates.store') }}@endif">
				{{ csrf_field() }}
				@if(isset($content))
					{{ method_field('PUT') }}
				@endif
				<input name="id" type="hidden" value="@if(isset($content->id)){{ $content->id }}@endif">

				<fieldset>
					<legend>Основные данные</legend>
					<div class="row-wrap">
						<label>
							@if(!isset($content) || isset($content->id))
								<input	name="title"
										class="input-text col_1_2"
										type="text"
										required="required"
										placeholder="Название&hellip;"
										value="@if(isset($content)){{ $content->title }}@endif">
								<span>Название</span>
							@else
								<span>Название: {{ $content->title }}</span>
							@endif
						</label>
					</div>
					<div class="row-wrap">
						<label>
							<input name="enabled" class="chbox-input" type="checkbox" @if(isset($content) && ($content->enabled == 1)) checked="checked" @endif @if(isset($content) && !isset($content->id)) disabled="disabled" @endif>
							<span>Опубликовать</span>
						</label>
					</div>
				</fieldset>

				<fieldset>
					<legend>Тело Шаблона</legend>
					@if(!isset($content) || isset($content->id))
					<div class="row-wrap">
						<ul class="buttons-wrapper">
							<li><button name="fieldset" class="small-text-button" type="button">Insert Fieldset</button></li>
							<li><button name="row" class="small-text-button" type="button">Insert Row Div</button></li>
							<li><button name="inputText" class="small-text-button" type="button">Insert Text Input</button></li>
							<li><button name="inputNumber" class="small-text-button" type="button">Insert Number Input</button></li>
							<li><button name="inputCheckbox" class="small-text-button" type="button">Insert Checkbox</button></li>
							<li><button name="inputRadio" class="small-text-button" type="button">Insert RadioButton</button></li>
							<li><button name="select" class="small-text-button" type="button">Insert Select</button></li>
							<li><button name="wysiwyg" class="small-text-button" type="button">Insert Wysiwyg</button></li>
							<li><button name="textarea" class="small-text-button" type="button">Insert Textarea</button></li>
							<li><button name="singleImage" class="small-text-button" type="button">Insert Image Browse</button></li>
							<li><button name="slider" class="small-text-button" type="button">Insert Slider</button></li>
							<li><button name="customSlider" class="small-text-button" type="button">Insert Custom Slider</button></li>
						</ul>
						<textarea name="html_content" class="text-area">@if(isset($content)){{ $content->html_content }}@endif</textarea>
					</div>
					@else
						{!! $content->html_content !!}
					@endif
				</fieldset>

				@if(isset($content))
					<div class="details">
						<p>Создан:
							@if(!empty($content->created_by) && !is_numeric($content->created_by))
								<em>{{ $content->created_by['name'] }} (<ins>{{ $content->created_by['email'] }}</ins>) {{ $content->created_at }}</em>
							@else
								Unknown
							@endif
						</p>
						<p>Изменен:
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
						<button name="save" class="button" type="submit">Сохранить</button>
					@endif
				</div>
			</form>
		</div>
	</div>
</div>
@stop