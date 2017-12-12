@extends('admin.layouts.default')
@section('scripts')
	<script type="text/javascript" src="{{ asset('js/admin/scripts/category.js') }}"></script>
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

			<form	name="category"
					method="post"
					target="_self"
					action="{{ route('admin.category.store') }}"
					enctype="multipart/form-data">
				{{ csrf_field() }}
				@if(isset($content->id))
					{{ method_field('PUT') }}
				@endif
				<input name="id" type="hidden" value="@if(isset($content->id)){{ $content->id }}@endif">
				<input name="category_type" type="hidden" value="{{ $category_type }}">

				<fieldset style="display: flex; justify-content: space-between; align-items: stretch">
					<legend>Основные данные</legend>
					<div class="col_1_2">
						<div class="row-wrap">
							<label>
								@if(!isset($content) || isset($content->id))
									<input	name="title"
											class="input-text col_4_5"
											type="text"
											required="required"
											placeholder="Название&hellip;"
											value="@if(isset($content)){{$content->title}}@endif">
									<span>Название</span>
								@else
									<span>Название: {{ $content->title }}</span>
								@endif
							</label>
						</div>
						<div class="row-wrap">
							<label>
								@if(!isset($content) || isset($content->id))
									<input	name="slug"
											class="input-text col_4_5"
											type="text"
											placeholder="Ссылка&hellip;"
											value="@if(isset($content)){{$content->slug}}@endif">
									<span>Ссылка</span>
								@else
									<span>Ссылка: {{ $content->slug }}</span>
								@endif
							</label>
						</div>
						<div class="row-wrap">
							<label>
								<select name="refer_to" class="input-text">
									<option value="0">Не отностся</option>
									@foreach($categories as $category)
										<option	value="{{ $category->id }}"@if(isset($content) && ($category->id == $content->refer_to)) selected="selected" @endif>
											{{ $category->title }}
										</option>
									@endforeach
								</select>
								<span>Отнести к категории</span>
							</label>
						</div>
						<div class="row-wrap">
							<label>
								<input name="enabled" class="chbox-input" type="checkbox" @if(isset($content) && ($content->enabled == 1)) checked="checked" @endif>
								<span>Опубликовать</span>
							</label>
						</div>
					</div>
					@if($options->image == 1)
					<div class="preview-image col_1_2">
						<div class="preview-image-wrap">
							@if(isset($content) && !empty($content->img_url))
								<img src="{{ $content->img_url->src }}" alt="{{ $content->img_url->alt }}" data-type="file">
							@endif
						</div>
						<noscript>
							<input name="image" class="input-file" type="file">
						</noscript>
					</div>
					@endif
				</fieldset>

				@if($options->text == 1)
				<fieldset>
					<legend>Текст</legend>
					<div class="row-wrap">
						<textarea name="text" class="text-area needCKE">@if(isset($content)){{ $content->text }}@endif</textarea>
					</div>
				</fieldset>
				@endif

				@if($options->meta == 1)
				<fieldset>
					<legend>Мета-данные</legend>
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
				@endif

				@if($options->seo == 1)
				<fieldset>
					<legend>SEO Данные</legend>
					<div class="row-wrap">
						<label>
							<input	name="need_seo"
									class="chbox-input"
									type="checkbox"
									@if(isset($content) && ($content->need_seo == 1)) checked="checked" @endif>
							<span>Использовать SEO для данной категории</span>
						</label>
					</div>
					<div class="row-wrap">
						<label>
							<input	name="seo_title"
									class="input-text col_1_2"
									type="text"
									placeholder="SEO Заглавие&hellip;"
									value="@if(isset($content)){{ $content->seo_title }}@endif">
							<span>SEO Заглавие</span>
						</label>
					</div>
					<div class="row-wrap">
						<p>SEO Текст</p>
						<textarea name="seo_text" class="text-area needCKE">@if(isset($content)){{ $content->seo_text }}@endif</textarea>
					</div>
				</fieldset>
				@endif

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
					<button name="save" class="button" type="submit">Сохранить</button>
				</div>
			</form>
		</div>
	</div>
</div>
@stop