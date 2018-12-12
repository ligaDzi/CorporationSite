    
    
    <div class="widget-first widget recent-posts">
        <h3>{{ Lang::get('ru.latest_projects') }}</h3>
        <div class="recent-post group">

            @if($portfolio)

                @foreach($portfolio as $item)
            
                <div class="hentry-post group">
                    <div class="thumb-img">
                        <img src="{{ asset( config('settings.theme')) }}/images/projects/{{ $item->img->mini }}" style="width:55px;" alt="001" title="001" />
                    </div>
                    <div class="text">
                        <a href="{{ route('portfolio.show', ['alias'=>$item->alias]) }}" title="{{ $item->title }}" class="title">{{ $item->title }}</a>
                        <p>{{ str_limit($item->text, 100) }} </p>
                        <a class="read-more" href="{{ route('portfolio.show', ['alias'=>$item->alias]) }}">&rarr; {{ Lang::get('ru.read_more') }}</a>
                    </div>
                </div>

                @endforeach

            @endif

        </div>
    </div>
        
    <div class="widget-last widget recent-comments">
        <h3>{{ Lang::get('ru.latest_comments') }}</h3>
        <div class="recent-post recent-comments group">

         @if($comments)

            @foreach($comments as $item)
        
            <div class="the-post group">
                <div class="avatar">
                <!-- 
                    Граватар это глобальная БД. На их сайте можно добавить свой аватар, который будет привязаг к email.
                    Когда я зарегистрируюсь на каком либо сайте, который поддержует граватар, с этим email, то аватарка подтянится сама.
                    Т.е. одна аватарка на многих сайтах; аватарка привязанная не к сайту, а к email.
                -->
                    @set($hash, ( $item->email ? md5($item->email) : md5($item->user->email) ))
                    <img alt="" src="https://www.gravatar.com/avatar/{{$hash}}?d=mm&s=55" class="avatar" />   
                </div>
                <span class="author">
                    <strong>
                        <a href="#">{{ isset($item->user) ? $item->user->name : $item->name}}</a>
                    </strong> in
                </span> 
                <a class="title" href="{{ route('articles.show', ['alias'=>$item->article->alias]) }}">
                    {{ $item->article->title }}
                </a>
                <p class="comment">
                    {{ $item->text }} 
                    <a class="goto" href="{{ route('articles.show', ['alias'=>$item->article->alias]) }}">&#187;</a>
                </p>
            </div>

            @endforeach
            
        @endif

        </div>
    </div>
            
    