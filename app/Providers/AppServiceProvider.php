<?php

namespace Corp\Providers;

use Illuminate\Support\ServiceProvider;

use Blade;
use DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        /* 
            Моя директива для шаблонов blade.
            С помощью этой директивы можно создавать переменные в шаблоне и рписваивать им значения.
            Метод explode(',', $exp) разбивает передаваемую строку $exp по разделителю ','
            причем первый элемент имя перменной, а второй значение.
            С помощью list($name, $val) создается переменная с именем равным первому элементу возвращенным ф-цией explode()
            и значением равным второму элементу возвращенным ф-цией explode().

            @set($i,1)            
        */
        Blade::directive('set', function($exp){
            list($name, $val) = explode(',', $exp);

            return "<?php $name = $val ?>";
        });

        /*
            Этот код позволяет отобразить все SQL-запросы к БД.
        */
        // DB::listen(function($query){

        //     echo '<h1>'.$query->sql.'</h1>';
        // });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
