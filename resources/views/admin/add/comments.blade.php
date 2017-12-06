@extends('admin.layouts.default')
@section('scripts')
	<script type="text/javascript" src="{{ asset('js/admin/scripts/comments.js') }}"></script>
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

			<form	name="comments"
					method="post"
					target="_self"
					@if(isset($content->id))action="{{ route('admin.comments.update',$content->id) }}"@endif>
				{{ csrf_field() }}
				{{ method_field('PUT') }}
				<input name="id" type="hidden" value="@if(isset($content->id)){{ $content->id }}@endif">

				<fieldset>
					<legend>Main data</legend>
					<div class="row-wrap">
						<label>
							<span>Comment creator: </span>
							<a href="{{ route('admin.users.show', $content->user_id->id) }}">
								{{ $content->user_id->name }} ({{ $content->user_id->email }})
							</a>
						</label>
					</div>
					<div class="row-wrap">
						<label>
							<span>Commented article: </span>
							<a href="{{ route('admin.'.$content->post_id->type.'.show', $content->post_id->id) }}">
								{{ $content->post_id->title }}
							</a>
							({{ $content->post_id->post_type }})
						</label>
					</div>
				</fieldset>

				@if(!empty($content->refer_to_comment))
				<fieldset>
					<legend>Parent comment</legend>
						<div class="row-wrap">
							<label>
								<div class="row-wrap" style="padding: 10px; font-size: 16px; border: 1px solid #eee;">{{ $content->refer_to_comment->text }}</div>
								<div class="row-wrap">Written by
									<a href="{{ route('admin.users.show', $content->refer_to_comment->user->id ) }}">
										{{ $content->refer_to_comment->user->name }} ({{ $content->refer_to_comment->user->email }})
									</a>
									at {{ $content->refer_to_comment->created }}
								</div>
							</label>
						</div>
				</fieldset>
				@endif

				<fieldset>
					<legend>Comment text</legend>
					<div class="row-wrap">
						@if(!isset($content) || isset($content->id))
						<textarea class="text-area" name="text" required="required">{{ $content->text }}</textarea>
						@else
						<p>{{ $content->text }}</p>
						@endif
					</div>
				</fieldset>

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