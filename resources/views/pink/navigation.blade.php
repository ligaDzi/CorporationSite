@if($menu)
    <div class="menu classic">
        <ul id="nav" class="menu">
            <!-- 
                Т.к. меню сложное, с множеством классов, 
                поэтому мы формируем его в ручеую используя объект меню созданный с помощью расширения laravel-menu.
            -->
            @include(config('settings.theme').'.customMenuItems',['items'=>$menu->roots()])
        </ul>
    </div>
@endif
