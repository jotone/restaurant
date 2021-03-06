@extends('admin.layouts.default')
@section('scripts')
	<script type="text/javascript" src="{{ asset('js/admin/scripts/meal_menu.js') }}"></script>
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

			<form	name="mealMenu"
					method="post"
					target="_self"
					action="@if(isset($content->id)){{ route('admin.menu.update', $content->id) }}@else{{ route('admin.menu.store') }}@endif"
					enctype="multipart/form-data">
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
							<select name="restaurant_id" class="input-text" @if(isset($content) && !isset($content->id)) disabled="disabled" @endif>
								<option value="0">Не относится</option>
								@foreach($restaurants as $restaurant)
									<option value="{{ $restaurant->id }}" @if(isset($content) && ($content->restaurant_id == $restaurant->id))selected="selected"@endif>{{ $restaurant->title }}</option>
								@endforeach
							</select>
							<span>Отнести к ресторану</span>
						</label>
					</div>
					@if($settings->category_type > 0)
						<div class="row-wrap">
							@if($settings->category_multiselect == 0)
								<label>
									<select name="category" class="input-text" @if(isset($content) && !isset($content->id)) disabled="disabled" @endif>
										<option value="0">Не относится</option>
										@foreach($categories as $category)
											<option value="{{ $category->id }}" @if(isset($content) && ($content->category_id == $category->id))selected="selected"@endif>{{ $category->title }}</option>
										@endforeach
									</select>
									<span>Категория меню</span>
								</label>
							@else
								<p>Отнести к категориям:</p>
								<div class="checkbox-group-wrap">
									@foreach($categories as $category)
										<div class="checkbox-group-item">
											<label>
												<input	name="category[]"
														type="checkbox"
														class="chbox-input"
														value="{{ $category['id'] }}"
														@if((isset($content)) && (in_array($category['id'], $content->category_id))) checked="checked" @endif>
												<span>{{ $category['title'] }}</span>
											</label>
										</div>
									@endforeach
								</div>
							@endif
						</div>
					@endif
					<div class="row-wrap">
						<label>
							<input	name="enabled"
									class="chbox-input"
									type="checkbox"
									@if(isset($content) && ($content->enabled == 1)) checked="checked" @endif
									@if(isset($content) && !isset($content->id)) disabled="disabled" @endif>
							<span>Опубликовать</span>
						</label>
					</div>
				</fieldset>

				<fieldset>
					<legend>Привязать блюда</legend>
					<div class="row-wrap">
						<div class="group-lists">
							@foreach($dishes as $dish)
								<ul>
									<li>
										<a href="#" class="fa fa-times"><strong>{{ $dish['caption'] }}</strong></a>
										<ul>
										@foreach($dish['items'] as $item)
											<li>
												<label style="width: 100%;">
													<input	type="checkbox"
															name="dish_ids[]"
															class="chbox-input"
															value="{{ $item['id'] }}"
															@if(isset($content) && !isset($content->id)) disabled="disabled" @endif
															@if(isset($content) && in_array($item['id'], $content->dishes)) checked="checked" @endif>
													<span>Название: <ins>{{ $item['title'] }}</ins></span>
													<span>Цена: <ins>{{ $item['price'] }}</ins></span>
												</label>
											</li>
										@endforeach
										</ul>
									</li>
								</ul>
							@endforeach
						</div>
					</div>
				</fieldset>

				@if($settings->slider == 1)
				<fieldset>
					<legend>Изображения</legend>
					<div class="slider-wrap">
						@if(!isset($content) || isset($content->id))
							<noscript>
								<input name="images[]" type="file" multiple="multiple">
							</noscript>
						@endif
						<div class="slider-controls-wrap">
							<div class="slider-images" @if(isset($content->img_url) && !empty($content->img_url)) style="display: flex" @endif>
								<div class="slider-control-elem"><span class="fa fa-angle-left"></span></div>
								<div class="slider-content-wrap">
									@if(isset($content->img_url) && !empty($content->img_url))
										@foreach($content->img_url as $image)
											<div class="slide-image-wrap @if($loop->first) active @endif">
												<div class="slide-container">
													<img src="{{ $image['src'] }}" alt="" data-type="file">
												</div>
												<div class="slide-alt-wrap">
													<input	name="altText"
															type="text"
															class="input-text"
															value="{{ $image['alt'] }}"
															placeholder="Альтернативый текст&hellip;">
													<span class="drop-image-icon fa fa-times"></span>
												</div>
											</div>
										@endforeach
									@endif
								</div>
								<div class="slider-control-elem"><span class="fa fa-angle-right"></span></div>
							</div>
							<div class="slider-buttons">
								@if(!isset($content) || isset($content->id))
									<input name="upload" type="file" style="display: none" multiple="multiple">
									<button name="uploadSliderImages" type="button" class="button">Обзор&hellip;</button>
									<button name="galleryOverview" type="button" class="button">Галерея&hellip;</button>
								@endif
							</div>
						</div>
						<div class="slider-previews-wrap">
							<ul>
								@if(isset($content))
									@foreach($content->img_url as $image)
										<li>
											<div class="controls">
												<div class="preview-controls fa fa-angle-up col_1 tac"></div>
												<div class="preview-controls fa fa-angle-down col_1 tac"></div>
											</div>
											<div class="preview-image">
												<img src="{{ asset($image['src']) }}" alt="">
											</div>
											<div class="preview-data">
												<p data-type="name">Filename: <span>{{ $image['name'] }}</span></p>
												<p data-type="size">Size: <span>{{ $image['size'] }}</span></p>
												<p data-type="alt">Alt: <span>{{ $image['alt'] }}</span></p>
											</div>
											<div class="drop-preview-icon fa fa-times"></div>
										</li>
									@endforeach
								@endif
							</ul>
						</div>
					</div>
				</fieldset>
				@endif

				@if($settings->text == 1)
				<fieldset>
					<legend>Текст</legend>
					<div class="row-wrap">
						@if(!isset($content) || isset($content->id))
							<textarea class="text-area" name="text">@if(isset($content)){{ $content->text }}@endif</textarea>
						@else
							@if(isset($content)){{ $content->text }}@endif
						@endif
					</div>
				</fieldset>
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