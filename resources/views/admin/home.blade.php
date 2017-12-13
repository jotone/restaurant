@extends('admin.layouts.default')
@section('scripts')
	<script type="text/javascript" src="{{ asset('js/admin/scripts/home.js') }}"></script>
@stop
@section('content')
	<div class="main-wrap">
		<input name="id" type="hidden">
		<div style="display: flex; align-items: flex-start; justify-content: flex-start;">
			<div id="step_0" style="padding: 60px 20px;">
				<p><input name="phone" type="text" class="input-text" placeholder="Phone"></p>
				<p><button class="button" type="button" name="send_phone">Send Phone</button></p>
			</div>

			<div id="step_1" style="padding: 60px 20px; display: none">
				<p><input name="sms_code" type="text" class="input-text" placeholder="SMS Code"></p>
				<p><button class="button" type="button" name="send_sms">Send SMS</button></p>
			</div>

			<div id="step_2" style="padding: 60px 20px; display: none">
				<p><input name="email" type="email" class="input-text" placeholder="E-mail"></p>
				<p><input name="name" type="text" class="input-text" placeholder="Name"></p>
				<p><input name="surname" type="text" class="input-text" placeholder="Surname"></p>
				<p><input name="password" type="password" class="input-text" placeholder="Password"></p>
				<p><input name="confirm_password" type="password" class="input-text" placeholder="Password confirmation"></p>
				<p><button class="button" type="button" name="save">Save</button></p>
			</div>
		</div>

		<div style="padding: 60px 0px;">
			<p><input name="user_login" type="email" placeholder="Email"></p>
			<p><input name="user_pass" type="password" placeholder="PASSWORD"></p>
			<p><button class="button" type="button" name="login">YARRR</button></p>
		</div>
	</div>
@stop