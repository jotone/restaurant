@extends('admin.layouts.default')
@section('scripts')
	<script type="text/javascript" src="{{ asset('js/admin/scripts/settings.js') }}"></script>
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

			<form name="settingsSave" target="_self" action="{{ route('admin.settings.update') }}" method="POST">
				{{ csrf_field() }}
				{{ method_field('PUT') }}
				@foreach($content as $item)
					<fieldset>
						<legend>{{ $item['title'] }}</legend>

						@foreach($item['options'] as $key => $value)
							<div class="row-wrap">
							@if($key == 'category_type')

								<label>
									<select name="{{ json_encode(['name'=>$key,'id'=>$item['id']]) }}" class="input-text">
										<option value="0">Do not accept categories</option>
										@if(!empty($categories))
											@foreach($categories as $category)
												<option value="{{ $category['id'] }}" @if($category['id'] == $value) selected="selected" @endif>{{ $category['title'] }}</option>
											@endforeach
										@endif
									</select>
									<span>Category type</span>
								</label>
							@elseif($key == 'default_characteristics')
								<label>
									<p style="line-height: 18px;">Enter {{ title_case(str_replace('_', ' ',$key)) }} with "," delimiter</p>
									<input	name="{{ json_encode(['name'=>$key,'id'=>$item['id']]) }}"
											type="text"
											class="input-text col_1_2"
											value="{{ $value }}"
											data-type="{{ $key }}"
											placeholder="{{ title_case(str_replace('_', ' ',$key)) }}&hellip;">
								</label>
							@else

								<label>
									<input	name="{{ json_encode(['name'=>$key,'id'=>$item['id']]) }}"
											type="checkbox"
											class="chbox-input"
											data-type="{{ $key }}"
											@if($value == 1)checked="checked"@endif>
									<span>Enable {{ title_case(str_replace('_', ' ',$key)) }}</span>
								</label>

							@endif
							</div>
						@endforeach

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