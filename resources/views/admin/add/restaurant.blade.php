@extends('admin.layouts.default')
@section('scripts')
	<script type="text/javascript" src="{{ asset('js/admin/scripts/restaurant.js') }}"></script>
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

			<form	name="restaurant"
					method="post"
					target="_self"
					action="@if(isset($content->id)){{ route('admin.restaurant.update', $content->id) }}@else{{ route('admin.restaurant.store') }}@endif"
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
							@if(!isset($content) || isset($content->id))
								<input	name="time_begin"
										autocomplete="off"
										class="input-text col_1_5"
										type="text"
										placeholder="00:00"
										pattern="[0-2][0-9]:[0-5][0-9]"
										value="@if(isset($content)){{ $content->work_time->begin }}@endif">
								<span>Время открытия</span>
							@else
								<span>Время открытия:</span>
							@endif
						</label>
					</div>
					<div class="row-wrap">
						<label>
							@if(!isset($content) || isset($content->id))
								<input	name="time_finish"
										autocomplete="off"
										class="input-text col_1_5"
										type="text"
										placeholder="00:00"
										pattern="[0-2][0-9]:[0-5][0-9]"
										value="@if(isset($content)){{ $content->work_time->finish }}@endif">
								<span>Время закрытия</span>
							@else
								<span>Время закрытия:</span>
							@endif
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
								<span>Категория ресторана</span>
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
					<legend>Адресс</legend>
					<div class="row-wrap">
						@if(!isset($content) || isset($content->id))
							<textarea class="text-area needCKE" name="address">@if(isset($content)){{ $content->address }}@endif</textarea>
						@else
							@if(isset($content)){{ $content->address }}@endif
						@endif
					</div>
				</fieldset>

				<fieldset>
					<legend>Лого</legend>
					<div class="row-wrap">
						<div class="preview-image" id="logo_img">
							<div class="preview-image-wrap">
								@if(isset($content) && !empty($content->logo_img))
									<img src="{{ $content->logo_img->src }}" alt="" data-type="file">
								@endif
							</div>
							<noscript>
								<input name="logo_img" class="input-file" type="file">
							</noscript>
						</div>
					</div>
				</fieldset>

				<fieldset>
					<legend>Квадратное изображение</legend>
					<div class="row-wrap">
						<div class="preview-image" id="square_img">
							<div class="preview-image-wrap">
								@if(isset($content) && !empty($content->square_img))
									<img src="{{ $content->square_img->src }}" alt="" data-type="file">
								@endif
							</div>
							<noscript>
								<input name="square_img" class="input-file" type="file">
							</noscript>
						</div>
					</div>
				</fieldset>

				<fieldset>
					<legend>Большое изображение</legend>
					<div class="row-wrap">
						<div class="preview-image" id="large_img">
							<div class="preview-image-wrap">
								@if(isset($content) && !empty($content->large_img))
									<img src="{{ $content->large_img->src }}" alt="" data-type="file">
								@endif
							</div>
							<noscript>
								<input name="large_img" class="input-file" type="file">
							</noscript>
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

				<fieldset>
					<legend>Меню</legend>
					<div class="row-wrap">
						<div class="group-lists">
							<ul>
								<li>
									<ul>
									@foreach($menus as $menu)
										<li>
											<label style="width: 100%;">
												<input	type="checkbox"
														name="menus[]"
														class="chbox-input"
														value="{{ $menu->id }}"
														@if(isset($content) && !isset($content->id)) disabled="disabled" @endif
														@if(isset($content) && in_array($menu['id'], $content->menus)) checked="checked" @endif>
												<span>Название: <ins>{{ $menu->title }}</ins></span>
											</label>
										</li>
									@endforeach
									</ul>
								</li>
							</ul>
						</div>
					</div>
				</fieldset>

				@if($settings->text == 1)
				<fieldset>
					<legend>Текст</legend>
					<div class="row-wrap">
						@if(!isset($content) || isset($content->id))
							<textarea class="text-area needCKE" name="text">@if(isset($content)){{ $content->text }}@endif</textarea>
						@else
							@if(isset($content)){{ $content->text }}@endif
						@endif
					</div>
				</fieldset>
				@endif

				<fieldset>
					<legend>Координаты</legend>
					<div class="row-wrap">
						<label>
							<input	name="coordinateX"
									class="input-text"
									type="text"
									placeholder="Координата Х&hellip;"
									value="@if(isset($content)){{ $content->coordinates->x }}@endif"
									@if(isset($content) && !isset($content->id))disabled="disabled"@endif>
							<span>Координата Х</span>
						</label>
					</div>
					<div class="row-wrap">
						<label>
							<input	name="coordinateY"
									class="input-text"
									type="text"
									placeholder="Координата Y&hellip;"
									value="@if(isset($content)){{ $content->coordinates->y }}@endif"
									@if(isset($content) && !isset($content->id))disabled="disabled"@endif>
							<span>Координата Y</span>
						</label>
					</div>
				</fieldset>

				<fieldset>
					<legend>Вторичные данные</legend>
					<div class="row-wrap">
						<label>
							<input	name="has_delivery"
									class="chbox-input"
									type="checkbox"
									@if(isset($content) && ($content->has_delivery == 1)) checked="checked" @endif
									@if(isset($content) && !isset($content->id)) disabled="disabled" @endif>
							<span>Есть доставка</span>
						</label>
					</div>
					<div class="row-wrap">
						<label>
							<input	name="has_wifi"
									class="chbox-input"
									type="checkbox"
									@if(isset($content) && ($content->has_wifi == 1)) checked="checked" @endif
									@if(isset($content) && !isset($content->id)) disabled="disabled" @endif>
							<span>Есть Wi-fi</span>
						</label>
					</div>
					<div class="row-wrap">
						<p>Рейтинг</p>
						<label>
							<input	name="likes"
									type="number"
									min="0"
									value="@if(isset($content)){{ $content->rating->p }}@endif"
									@if(isset($content) && !isset($content->id))disabled="disabled"@endif>
							<span class="fa fa-thumbs-o-up"></span>
						</label>
						<label>
							<input	name="dislikes"
									type="number"
									min="0"
									value="@if(isset($content)){{ $content->rating->n }}@endif"
									@if(isset($content) && !isset($content->id))disabled="disabled"@endif>
							<span class="fa fa-thumbs-o-down"></span>
						</label>
					</div>
				</fieldset>

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