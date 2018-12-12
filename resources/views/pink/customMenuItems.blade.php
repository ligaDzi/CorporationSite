
@foreach($items as $item)
    <!-- Формирование главных пунктов меню -->
    <li {{ (URL::current() == $item->url()) ? "class=active" : "" }}> <!-- Подсветить активный пункт меню -->
        <a href="{{ $item->url() }}">{{ $item->title }}</a>

        <!-- Формирование подпунктов меню -->
        @if($item->hasChildren())
            <ul class="sub-menu">
                <!-- Рекурсия. Здесь вызывается этот же макет, только для дочерних пунктов меню. -->
                @include(config('settings.theme').'.customMenuItems',['items'=>$item->children()])
            </ul>
        @endif

    </li> 
@endforeach