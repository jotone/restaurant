@extends('admin.layouts.default')
@section('scripts')
	<script type="text/javascript" src="{{ asset('js/admin/scripts/products.js') }}"></script>
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

			<form	name="products"
					method="post"
					target="_self"
					action="@if(isset($content->id)){{ route('admin.products.update', $content->id) }}@else{{ route('admin.products.store') }}@endif"
					enctype="multipart/form-data">
				{{ csrf_field() }}
				@if(isset($content))
					{{ method_field('PUT') }}
				@endif
				<input name="id" type="hidden" value="@if(isset($content->id)){{ $content->id }}@endif">

				<fieldset>
					<legend>Main data</legend>
					<div class="row-wrap">
						<label>
							@if(!isset($content) || isset($content->id))
								<input	name="title"
										class="input-text col_1_2"
										type="text"
										required="required"
										placeholder="Title&hellip;"
										value="@if(isset($content)){{ $content->title }}@endif">
								<span>Title</span>
							@else
								<span>Title: {{ $content->title }}</span>
							@endif
						</label>
					</div>

					<div class="row-wrap">
						<label>
							@if(!isset($content) || isset($content->id))
								<input	name="slug"
										class="input-text col_1_2"
										type="text"
										placeholder="Link&hellip;"
										value="@if(isset($content)){{ $content->slug }}@endif">
								<span>Link</span>
							@else
								<span>Link: {{ $content->slug }}</span>
							@endif
						</label>
					</div>

					@if($settings->vendor_code == 1)
					<div class="row-wrap">
						<label>
							@if(!isset($content) || isset($content->id))
								<input	name="vendor_code"
										class="input-text col_1_2"
										type="text"
										placeholder="Vendor Code&hellip;"
										value="@if(isset($content)){{ $content->vendor_code }}@endif">
								<span>Vendor Code</span>
							@else
								<span>Vendor Code: {{ $content->vendor_code }}</span>
							@endif
						</label>
					</div>
					@endif

					<div class="row-wrap">
						<label>
							@if(!isset($content) || isset($content->id))
								<input	name="price"
										class="input-text col_1_2"
										type="text"
										pattern="[0-9,\.]+"
										placeholder="Price&hellip;"
										value="@if(isset($content)){{ $content->price }}@endif">
								<span>Price</span>
							@else
								<span>Price: {{ $content->price }}</span>
							@endif
						</label>
					</div>

					@if($settings->quantity == 1)
					<div class="row-wrap">
						<label>
							@if(!isset($content) || isset($content->id))
								<input	name="quantity"
										class="input-text col_1_2"
										type="number"
										min="0"
										value="@if(isset($content)){{$content->quantity}}@else{{'0'}}@endif">
								<span>Quantity</span>
							@else
								<span>Quantity: {{ $content->quantity }}</span>
							@endif
						</label>
					</div>
					@endif

					@if($settings->category_type != 0)
					<div class="row-wrap">
						@if($settings->category_multiselect == 0)
						<label>
							<select	name="category"
									class="input-text col_1_2"
									@if(isset($content) && !isset($content->id)) disabled="disabled" @endif>
								<option value="0" @if(isset($content->id) && ($content->category_id == 0)) selected="selected" @endif>
									No category selected
								</option>
								@foreach($categories as $category)
									<option value="{{ $category['id'] }}" @if(isset($content->id) && (in_array($category['id'], $content->category_id))) selected="selected" @endif>
										{{ $category['title'] }}
									</option>
								@endforeach
							</select>
							<span>Belong to category</span>
						</label>
						@else
							<p>Belongs to categories:</p>
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
							<span>Enabled</span>
						</label>
					</div>
				</fieldset>

				@if($settings->slider == 1)
				<fieldset>
					<legend>Slider</legend>
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
													<input name="altText" type="text" class="input-text" value="{{ $image['alt'] }}" placeholder="Alternative text&hellip;">
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
									<button name="uploadSliderImages" type="button" class="button">Browse&hellip;</button>
									<button name="galleryOverview" type="button" class="button">Gallery&hellip;</button>
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

				@if($settings->description == 1)
				<fieldset>
					<legend>Description</legend>
					<div class="row-wrap">
						@if(!isset($content) || isset($content->id))
							<textarea name="description" class="text-area needCKE">@if(isset($content)){{ $content->description }}@endif</textarea>
						@else
							{!! $content->description !!}
						@endif
					</div>
				</fieldset>
				@endif

				@if($settings->text == 1)
				<fieldset>
					<legend>Text</legend>
					<div class="row-wrap">
						@if(!isset($content) || isset($content->id))
							<textarea name="text" class="text-area needCKE">@if(isset($content)){{ $content->text }}@endif</textarea>
						@else
							{!! $content->text !!}
						@endif
					</div>
				</fieldset>
				@endif

				@if($settings->tags == 1)
				<fieldset>
					<legend>Tags</legend>
					<div class="row-wrap">
						<label class="helper-wrap">
							<input	name="tags"
									class="input-text col_4_5"
									autocomplete="off"
									value="@if(isset($content)){{ $content->tags }}@endif"
									@if(isset($content) && !isset($content->id)) disabled="disabled" @endif>
							<span>Enter tags with "," delimiter</span>
							<ul class="list"></ul>
						</label>
					</div>
				</fieldset>
				@endif

				@if($settings->characteristics_table == 1)
				<fieldset>
					<legend>Characteristics</legend>
					<div class="row-wrap">
						<table class="items-list characteristic-table">
							<thead>
								<tr>
									<th></th>
									<th>Caption</th>
									<th>Value</th>
								</tr>
							</thead>
							<tbody>
							@if(!isset($content))
								<?php $default_characteristics = explode(',', $settings->default_characteristics); ?>
								@foreach($default_characteristics as $title)
									<tr>
										<td><a class="delete fa fa-times" href="#"></a></td>
										<td>
											<input	name="rowCaption[]"
													type="text"
													class="input-text col_1"
													placeholder="Caption&hellip;"
													value="{{ trim($title) }}">
										</td>
										<td><input name="rowValue[]" type="text" class="input-text col_1" placeholder="Value&hellip;"></td>
									</tr>
								@endforeach
							@else

								@foreach($content->characteristic as $characteristic)
									<tr>
										<td><a class="delete fa fa-times" href="#"></a></td>
										<td>
											<input	name="rowCaption[]"
													type="text"
													class="input-text col_1"
													placeholder="Caption&hellip;"
													value="{{ $characteristic->key }}"
													@if(isset($content) && !isset($content->id)) disabled="disabled" @endif>
										</td>
										<td>
											<input	name="rowValue[]"
													type="text"
													class="input-text col_1"
													placeholder="Value&hellip;"
													value="{{ $characteristic->val }}"
													@if(isset($content) && !isset($content->id)) disabled="disabled" @endif>
										</td>
									</tr>
								@endforeach
							@endif
							</tbody>
						</table>
						<div class="row-wrap"></div>
					</div>
				</fieldset>
				@endif

				@if($settings->meta_data == 1)
				<fieldset>
					<legend>Meta Data</legend>
					<div class="row-wrap">
						<label>
							<input	name="meta_title"
									class="input-text col_1_2"
									type="text"
									placeholder="Meta Title&hellip;"
									value="@if(isset($content)){{ $content->meta_title }}@endif"
									@if(isset($content) && !isset($content->id)) disabled="disabled" @endif>
							<span>Meta Title</span>
						</label>
					</div>
					<div class="row-wrap">
						<p>Meta Description</p>
						@if(!isset($content) || isset($content->id))
							<textarea name="meta_description" class="text-area-middle">@if(isset($content)){{ $content->meta_description }}@endif</textarea>
						@else
							{!! $content->meta_description !!}
						@endif
					</div>
					<div class="row-wrap">
						<p>Meta Keywords</p>
						@if(!isset($content) || isset($content->id))
							<textarea name="meta_keywords" class="text-area-middle">@if(isset($content)){{ $content->meta_keywords }}@endif</textarea>
						@else
							{!! $content->meta_keywords !!}
						@endif
					</div>
				</fieldset>
				@endif

				@if($settings->seo_data == 1)
				<fieldset>
					<legend>SEO data</legend>
					<div class="row-wrap">
						<label>
							<input	name="seo_title"
									class="input-text col_1_2"
									type="text"
									placeholder="SEO Title&hellip;"
									value="@if(isset($content)){{ $content->seo_title }}@endif"
									@if(isset($content) && !isset($content->id)) disabled="disabled" @endif>
							<span>SEO Title</span>
						</label>
					</div>
					<div class="row-wrap">
						<p>SEO text</p>
						@if(!isset($content) || isset($content->id))
							<textarea name="seo_text" class="text-area needCKE">@if(isset($content)){{ $content->seo_text }}@endif</textarea>
						@else
							{!! $content->seo_text !!}
						@endif
					</div>
				</fieldset>
				@endif

				@if(isset($content))
					<div class="details">
						<p>Created by:
							@if(!empty($content->created_by) && !is_numeric($content->created_by))
								<em>{{ $content->created_by['name'] }} (<ins>{{ $content->created_by['email'] }}</ins>) {{ $content->created_at }}</em>
							@else
								Unknown
							@endif
						</p>
						<p>Updated by:
							@if(!empty($content->updated_by) && !is_numeric($content->updated_by))
								<em>{{ $content->updated_by['name'] }} (<ins>{{ $content->updated_by['email'] }}</ins>) {{ $content->updated_at }}</em>
							@else
								Unknown
							@endif
						</p>
					</div>
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