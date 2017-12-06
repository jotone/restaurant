@extends('admin.layouts.default')
@section('scripts')
	<script type="text/javascript" src="{{ asset('js/admin/scripts/main_info.js') }}"></script>
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

				<form name="mainInfo" target="_self" action="{{ route('admin.main_info.update') }}" method="post">
					{{ csrf_field() }}
					{{ method_field('PUT') }}
					@foreach($content as $item)
						<fieldset>
							<legend>{{ $item['title'] }}</legend>
							@if($item['slug'] == 'text')

								@foreach($item['options'] as $option)
									<div class="row-wrap">
										<label>
											<input	name="{{ json_encode(['name'=>$item['slug'], 'id'=>$item['id']]) }}[]"
													type="text" class="input-text col_1_2"
													value="{{ $option }}"
													placeholder="{{ $item['title'] }}&hellip;">
											<span>{{ $item['title'] }}</span>
										</label>
									</div>
								@endforeach

								<div class="row-wrap">
									<label>
										<input	name="{{ json_encode(['name'=>$item['slug'], 'id'=>$item['id']]) }}[]"
												type="text" class="input-text col_1_2"
												placeholder="{{ $item['title'] }}&hellip;">
										<span>{{ $item['title'] }}</span>
									</label>
								</div>
								<div class="row-wrap">
									<button name="add" type="button" class="button">Add</button>
								</div>

							@elseif($item['slug'] == 'wysiwyg')

								@foreach($item['options'] as $option)
									<div class="row-wrap">
										<p>{{ $item['title'] }}</p>
										<textarea name="{{ json_encode(['name'=>$item['slug'], 'id'=>$item['id']]) }}[]" class="text-area-middle">{{ $option }}</textarea>
									</div>
								@endforeach

								<div class="row-wrap">
									<p>{{ $item['title'] }}</p>
									<textarea name="{{ json_encode(['name'=>$item['slug'], 'id'=>$item['id']]) }}[]" class="text-area-middle"></textarea>
								</div>
								<div class="row-wrap">
									<button name="add" type="button" class="button" data-type="{{ $item['slug'] }}">Add</button>
								</div>

							@elseif($item['slug'] == 'coordinates')
								@for($i =0 ; $i < count($item['options']->x); $i++)
									<div class="row-wrap">
										<span>X coordinate: </span>
										<input	name="{{ json_encode(['name'=>$item['slug'], 'id'=>$item['id'], 'axis'=>'x']) }}[]"
												class="input-text-small col_1_10"
												style="margin-right: 10px;"
												placeholder="X&hellip;"
												value="{{ $item['options']->x[$i] }}">

										<span>Y coordinate: </span>
										<input	name="{{ json_encode(['name'=>$item['slug'], 'id'=>$item['id'], 'axis'=>'y']) }}[]"
												class="input-text-small col_1_10"
												style="margin-right: 10px;"
												placeholder="Y&hellip;"
												value="{{ $item['options']->y[$i] }}">

										<span>Z coordinate: </span>
										<input	name="{{ json_encode(['name'=>$item['slug'], 'id'=>$item['id'], 'axis'=>'z']) }}[]"
												class="input-text-small col_1_10"
												placeholder="Z&hellip;"
												value="{{ $item['options']->z[$i] }}">
									</div>
								@endfor
								<div class="row-wrap">
									<span>X coordinate: </span>
									<input	name="{{ json_encode(['name'=>$item['slug'], 'id'=>$item['id'], 'axis'=>'x']) }}[]"
											class="input-text-small col_1_10"
											style="margin-right: 10px;"
											data-type="x"
											placeholder="X&hellip;">

									<span>Y coordinate: </span>
									<input	name="{{ json_encode(['name'=>$item['slug'], 'id'=>$item['id'], 'axis'=>'y']) }}[]"
											class="input-text-small col_1_10"
											style="margin-right: 10px;"
											data-type="y"
											placeholder="Y&hellip;">

									<span>Z coordinate: </span>
									<input	name="{{ json_encode(['name'=>$item['slug'], 'id'=>$item['id'], 'axis'=>'z']) }}[]"
											class="input-text-small col_1_10"
											data-type="z"
											placeholder="Z&hellip;">
								</div>
								<div class="row-wrap">
									<button name="add" type="button" class="button">Add</button>
								</div>
							@endif
						</fieldset>
					@endforeach

					<div class="form-button-wrap">
						<button name="save" class="button" type="submit">Save</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@stop