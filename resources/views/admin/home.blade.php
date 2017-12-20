@extends('admin.layouts.default')
@section('scripts')
	<script type="text/javascript" src="{{ asset('js/admin/scripts/home.js') }}"></script>
@stop
@section('content')
	<div class="main-wrap">
		<?php
		$users = \App\Visitors::select('id','name','surname')->get();
		$restaurants = \App\Restaurant::select('id','title')->get();
		?>
		<div>
			<select name="user">
				@foreach($users as $user)
				<option value="{{ \Illuminate\Support\Facades\Crypt::encrypt($user->id) }}">{{ $user->name }} {{ $user->surname }}</option>
				@endforeach
			</select>

			@foreach($restaurants as $restaurant)
				<div class="rest" data-rest_id="{{ $restaurant->id }}">
					<h2>{{ $restaurant->title }}</h2>

					<?php
					$menus = \App\MealMenu::select('dishes')->where('restaurant_id','=',$restaurant->id)->get();
					$dishes_list = [];
					foreach($menus as $menu){
						$dishes = json_decode($menu->dishes);
						$dishes_list = array_merge($dishes_list, $dishes);
					}

					$dishes_list = array_values(array_unique($dishes_list));

					$dishes = \App\MealDish::select('id','title','price')->whereIn('id',$dishes_list)->get();
					?>
					@foreach($dishes as $dish)
						<div>
							<input name="dish" value="{{ $dish->id }}" type="checkbox">
							<input name="quantity" type="number" value="0" min="0">&nbsp;{{ $dish->title }}&mdash;{{ $dish->price }}
						</div>
					@endforeach
				</div>
			@endforeach
			<button name="apply" type="button" class="button">Apply</button>
		</div>
	</div>
@stop