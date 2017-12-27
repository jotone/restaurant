@extends('admin.layouts.default')
@section('scripts')
	<script type="text/javascript" src="{{ asset('js/admin/scripts/visitors.js') }}"></script>
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

			<form	name="visitors"
					method="post"
					target="_self"
					action="@if(isset($content->id)){{ route('admin.visitors.update', $content->id) }}@else{{ route('admin.visitors.store') }}@endif">
				{{ csrf_field() }}
				@if(isset($content->id))
					{{ method_field('PUT') }}
				@endif
				<input name="id" type="hidden" value="@if(isset($content->id)){{ $content->id }}@endif">

				<fieldset>
					<legend>Основные данные</legend>
					<div class="row-wrap">
						<label>
							@if(!isset($content) || isset($content->id))
								<input	name="name"
										class="input-text col_1_2"
										type="text"
										required="required"
										placeholder="Имя&hellip;"
										value="@if(isset($content)){{ $content->name }}@endif">
								<span>Имя</span>
							@else
								<span>Имя: {{ $content->name }}</span>
							@endif
						</label>
					</div>
					<div class="row-wrap">
						<label>
							@if(!isset($content) || isset($content->id))
								<input	name="surname"
										class="input-text col_1_2"
										type="text"
										required="required"
										placeholder="Фамилия&hellip;"
										value="@if(isset($content)){{ $content->surname }}@endif">
								<span>Фамилия</span>
							@else
								<span>Фамилия: {{ $content->surname }}</span>
							@endif
						</label>
					</div>
					<div class="row-wrap">
						<label>
							@if(!isset($content) || isset($content->id))
								<input	name="phone"
										class="input-text col_1_2"
										type="text"
										required="required"
										placeholder="Телефон&hellip;"
										value="@if(isset($content)){{ $content->phone }}@endif">
								<span>Телефон</span>
							@else
								<span>Телефон: {{ $content->phone }}</span>
							@endif
						</label>
					</div>
					<div class="row-wrap">
						<label>
							@if(!isset($content) || isset($content->id))
								<input	name="email"
										class="input-text col_1_2"
										type="email"
										required="required"
										placeholder="E-mail&hellip;"
										value="@if(isset($content)){{ $content->email }}@endif">
								<span>E-mail</span>
							@else
								<span>E-mail: {{ $content->email }}</span>
							@endif
						</label>
					</div>
					@if(!isset($content))
						<div class="row-wrap">
							<label>
								<input	name="password"
										class="input-text col_1_2"
										type="password"
										required="required"
										placeholder="Пароль&hellip;"
										pattern=".{6,}">
								<span>Пароль</span>
							</label>
						</div>
						<div class="row-wrap">
							<label>
								<input	name="password_confirmation"
										class="input-text col_1_2"
										type="password"
										required="required"
										placeholder="Подтверждение пароля&hellip;"
										pattern=".{6,}">
								<span>Подтверждение пароля</span>
							</label>
						</div>
					@endif
				</fieldset>

				@if(isset($content->id))
					<fieldset>
						<legend>Изменить пароль</legend>
						<div class="row-wrap">
							<label>
								<input	name="password"
										class="input-text col_1_4"
										type="password"
										placeholder="Новый пароль&hellip;"
										pattern=".{6,}">
								<span>Новый пароль</span>
							</label>
						</div>
						<div class="row-wrap">
							<label>
								<input	name="password_confirmation"
										class="input-text col_1_4"
										type="password"
										placeholder="Подтверждение пароля&hellip;"
										pattern=".{6,}">
								<span>Подтверждение пароля</span>
							</label>
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