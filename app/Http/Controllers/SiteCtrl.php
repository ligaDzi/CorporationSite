<?php

namespace Corp\Http\Controllers;

use Illuminate\Http\Request;

use Corp\Repositories\MenuRepository;
use Corp\Repositories\SliderRepository;
use Menu;
use Slider;

/* Базовый контроллер */
class SiteCtrl extends Controller
{
    /* Метатег keywords <meta name="keywords" ...">. HTML-тег для указания ключевых слов страницы. */
    protected $keywords;

    /* Метатег description <meta name="description" ...">. HTML-тег, содержащий краткое описание страницы для поисковых роботов. */
    protected $meta_desc;

    /* Заголовок сайта, отображающийся на вкладке браузера */
    protected $title;
    
    /* Объект класса PortfolioRepository */
    protected $p_rep;

    /* Объект класса SliderRepository */
    protected $s_rep;

    /* Объект класса ArticleRepository */
    protected $a_rep;

    /* Объект класса MenuRepository */
    protected $m_rep;

    /* Имя шаблона */
    protected $teamplate;

    /* Массив переменных передающихся в шаблон */
    protected $vars = [];

    /* Переменная отвечает за то, отображать ли siteBar и где (слева, справа) его отбражать в шаблоне */
    protected $bar = 'no';

    /* Информация правого siteBar (правой колонки на сайте), если он есть */
    protected $contentRightBar = false;

    /* Информация левого siteBar, если он есть */
    protected $contentLeftBar = false;

    public function __construct(MenuRepository $menuRep){
        $this->m_rep = $menuRep;
    }

    /* Метод генерирующий шаблон */
    protected function renderOutput(){
        /* 
            Здесь формируется меню. 
            Сделано через переменную $navigation для того чтобы не подключать меню в каждем шаблоне (@include('navigation')).            
        */
        $menu = $this->getMenu();
        $navigation = view(config('settings.theme').'.navigation')->with('menu', $menu)->render();
        $this->vars['navigation'] = $navigation;

        /* 
            Здесь формируется правая колонка на сайте для всех страниц.             
        */
        if($this->contentRightBar){
            $rightBar = view(config('settings.theme').'.rightBar')->with('content_rightBar', $this->contentRightBar)->render();
            $this->vars['rightBar'] = $rightBar;
        }

        /* 
            Здесь формируется левая колонка на сайте для всех страниц.             
        */
        if($this->contentLeftBar){
            $leftBar = view(config('settings.theme').'.leftBar')->with('content_leftBar', $this->contentLeftBar)->render();
            $this->vars['leftBar'] = $leftBar;
        }

        /* 
            Здесь указывается будет ли колонка на странице и если будет то где (справа или слева).
            С помощью этой переменной подключаются различные классы к <div> с id="primary" в site.blade.php.             
        */
        $this->vars['bar'] = $this->bar;
        
        /* 
            Здесь формируется подвал для всех страниц.             
        */
        $footer = view(config('settings.theme').'.footer')->render();
        $this->vars['footer'] = $footer;

        $this->vars['keywords'] = $this->keywords;
        $this->vars['meta_desc'] = $this->meta_desc;
        $this->vars['title'] = $this->title;

        return view($this->teamplate)->with($this->vars);
    }

    /* Метод создаёт меню */
    public function getMenu(){

        $menu = $this->m_rep->get();
        /* 
            Использование расширения Laravel - laravel-menu.
            Здесь происходит создание меню на базе данных вытащенных из БД
            и расширения laravel-menu.
        */
        $mBuilder = Menu::make('MyNav', function($m) use($menu){

            foreach ($menu as $item) {

                /* Главные пункты меню */
                if ($item->parent == 0){
                    $m->add($item->title, $item->path)->id($item->id);
                }
                /* Дочерние пункты меню */
                else{
                    if($m->find($item->parent)){
                                                
                        $m->find($item->parent)
                          ->add($item->title, $item->path)
                          ->id($item->id);                        
                    }
                }
            }
        });

        return $mBuilder;
    }
}
