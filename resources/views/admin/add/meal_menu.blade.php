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
						@if($allow_categories > 0)
							<div class="row-wrap">
								<label>
									<select name="category_id" class="input-text" @if(isset($content) && !isset($content->id)) disabled="disabled" @endif>
										<option value="0">Не относится</option>
										@foreach($categories as $category)
											<option value="{{ $category->id }}" @if(isset($content) && ($content->category_id == $category->id))selected="selected"@endif>{{ $category->title }}</option>
										@endforeach
									</select>
									<span>Категория меню</span>
								</label>
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
															          name="dish_id[]"
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