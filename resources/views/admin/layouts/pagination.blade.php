<form name="searchForm" class="search-wrap" method="GET" action="{{ asset($page) }}">
	<input class="search-field" type="text" name="search" pattern="[0-9]+|.{0}|.{3,}">
	<input type="hidden" name="page" value="1">
	<input type="hidden" name="sort_by" value="id">
	<input type="hidden" name="dir" value="asc">
	<button type="submit" class="search-button">
		<span class="fa fa-search"></span>
	</button>
</form>
<ul class="pagination">
	@if($pagination['current_page'] > 1)
		<li><a href="{{ asset($page.'/?page=1&sort_by='.$pagination['sort_by'].'&dir='.$pagination['dir']) }}">&laquo;</a></li>
		<li><a href="{{ asset($page.'/?page='.($pagination['current_page'] -1).'&sort_by='.$pagination['sort_by'].'&dir='.$pagination['dir']) }}">&lsaquo;</a></li>
	@endif
	@for($i=1; $i<=$pagination['last_page']; $i++)
		<li><a href="{{ asset($page.'/?page='.$i.'&sort_by='.$pagination['sort_by'].'&dir='.$pagination['dir']) }}" @if($pagination['current_page'] == $i) class="active" @endif>{{$i}}</a></li>
	@endfor
	@if($pagination['current_page'] < $pagination['last_page'])
		<li><a href="{{ asset($page.'/?page='.($pagination['current_page'] +1).'&sort_by='.$pagination['sort_by'].'&dir='.$pagination['dir']) }}">&rsaquo;</a></li>
		<li><a href="{{ asset($page.'/?page='.$pagination['last_page'].'&sort_by='.$pagination['sort_by'].'&dir='.$pagination['dir']) }}">&raquo;</a></li>
	@endif
</ul>