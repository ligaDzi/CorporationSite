<!-- Макет правой колонки на главной странице -->
<div class="widget-first widget recent-posts">

@if($articles)
	<!-- Здесь используется переменная из локализациии (resources/lang/(ru или en)/ru.php) -->
	<h3>{{ trans('ru.from_blog') }}</h3>
	<div class="recent-post group">

	@foreach($articles as $article)

	    <div class="hentry-post group">
	        <div class="thumb-img"><img src="{{ asset(config('settings.theme')) }}/images/articles/{{ $article->img->mini }}" alt="001" title="001" /></div>
	        <div class="text">
	            <a href="{{ route('articles.show', ['alias'=>$article->alias]) }}" title="{{ $article->title }}" class="title">{{ $article->title }}</a>
	            <p class="post-date">{{ $article->created_at->format('F d, Y') }}</p>
	        </div>
	    </div>

	@endforeach

	</div>
@endif

</div>

<div class="widget-last widget text-image">
    <h3>Customer support</h3>
    <div class="text-image" style="text-align:left"><img src="{{ asset(config('settings.theme')) }}/images/callus.gif" alt="Customer support"/></div>
    <p>Proin porttitor dolor eu nibh lacinia at ultrices lorem venenatis. Sed volutpat scelerisque vulputate. </p>
</div>

	