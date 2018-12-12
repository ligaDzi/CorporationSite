
@foreach($items as $item)

    <li id="li-comment-{{ $item->id }}" class="comment even {{ ($item->user_id == $article->user_id) ? 'bypostauthor odd' : '' }}">
        <div id="comment-{{ $item->id }}" class="comment-container">
            <div class="comment-author vcard">
    		    <!-- 
    			    Граватар это глобальная БД. На их сайте можно добавить свой аватар, который будет привязаг к email.
    			    Когда я зарегистрируюсь на каком либо сайте, который поддержует граватар, с этим email, 
    				то аватарка подтянится сама.
    			    Т.е. одна аватарка на многих сайтах; аватарка привязанная не к сайту, а к email.
    			-->
    			@set($hash, ( isset($item->email) ? md5($item->email) : md5($item->user->email) ))						
                <img alt="" src="https://www.gravatar.com/avatar/{{$hash}}?d=mm&s=75" class="avatar" height="75" width="75" />
                <cite class="fn">{{ isset($item->user->name) ? $item->user->name : $item->name}}</cite>                 
            </div>
            <!-- .comment-author .vcard -->
            <div class="comment-meta commentmetadata">
                <div class="intro">
                    <div class="commentDate">
                        <a href="#comment-2">
                        {{ is_object($item->created_at) ? $item->created_at->format('F d, Y \a\t H:i') : '' }}</a>                        
                    </div>
                    <div class="commentNumber">#&nbsp;</div>
                </div>
                <div class="comment-body">
                    <p>{{ $item->text }}</p>
                </div>
                <div class="reply group">
                    <!-- Метод moveForm() дает возможность ответить на комментарий -->
                    <a class="comment-reply-link" href="#respond" onclick="return addComment.moveForm(&quot;comment-{{ $item->id }}&quot;, &quot;{{ $item->id }}&quot;,   &quot;respond&quot;, &quot;{{ $item->article_id }}&quot;)">{{ Lang::get('ru.reply_comment') }}</a>                    
                </div>
                <!-- .reply -->
            </div>
            <!-- .comment-meta .commentmetadata -->
        </div>
        <!-- #comment-##  -->
        <!-- Вывод дочерних комментариев. Здесь используется рекурсия  -->
        @if(isset($com[$item->id]))

            <ul class="children">
                @include(config('settings.theme').'.comment', ['items'=>$com[$item->id]]) 			<!-- Вывести комментарии. Используя рекурсию. -->              
            </ul>

        @endif

    </li>

@endforeach