@extends('admin.layouts.default')
@section('scripts')
	<script type="text/javascript" src="{{ asset('js/admin/scripts/users.js') }}"></script>
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

			<form	name="user"
					method="post"
					target="_self"
					action="@if(isset($content->id)){{ route('admin.users.update', $content->id) }}@else{{ route('admin.users.store') }}@endif">
				{{ csrf_field() }}
				@if(isset($content->id))
					{{ method_field('PUT') }}
				@endif
				<input name="id" type="hidden" value="@if(isset($content->id)){{ $content->id }}@endif">

				<fieldset>
					<legend>Main Data</legend>
					<div class="row-wrap">
						<label>
							@if(!isset($content) || isset($content->id))
								<input	name="name"
										class="input-text col_1_2"
										type="text"
										required="required"
										placeholder="Name&hellip;"
										value="@if(isset($content)){{ $content->name }}@endif">
								<span>Name</span>
							@else
								<span>Name: {{ $content->name }}</span>
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
										placeholder="Password&hellip;"
										pattern=".{6,}">
								<span>Password</span>
							</label>
						</div>
						<div class="row-wrap">
							<label>
								<input	name="password_confirmation"
										class="input-text col_1_2"
										type="password"
										required="required"
										placeholder="Confirm Password&hellip;"
										pattern=".{6,}">
								<span>Confirm Password</span>
							</label>
						</div>
					@endif
				</fieldset>

				<fieldset>
					<legend>Role</legend>
					<div class="row-wrap">
						<label>
							<select name="role" class="input-text" @if(isset($content) && !isset($content->id))disabled="disabled"@endif>
								<option value="0">Without role</option>
								@foreach($roles as $role)
									<option value="{{ $role->id }}" @if(isset($content) && ($content->role == $role->slug))selected="selected"@endif>{{ $role->title }}</option>
								@endforeach
							</select>
							<span>Role</span>
						</label>
					</div>
				</fieldset>

				@if(isset($content->id))
					<fieldset>
						<legend>Changing password</legend>
						<div class="row-wrap">
							<label>
								<input	name="password"
										class="input-text col_1_4"
										type="password"
										placeholder="New password&hellip;"
										pattern=".{6,}">
								<span>New password</span>
							</label>
						</div>
						<div class="row-wrap">
							<label>
								<input	name="password_confirmation"
										class="input-text col_1_4"
										type="password"
										placeholder="Confirm new password&hellip;"
										pattern=".{6,}">
								<span>Confirm new password</span>
							</label>
						</div>
					</fieldset>
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
