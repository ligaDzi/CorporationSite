<?php

namespace Corp\Http\Controllers;

use Illuminate\Http\Request;


use Config;
use Corp\Menu;
use Corp\Slider;
use Corp\Portfolio;
use Corp\Article;
use Corp\Repositories\MenuRepository;
use Corp\Repositories\SliderRepository;
use Corp\Repositories\PortfolioRepository;
use Corp\Repositories\ArticleRepository;
use Corp\Repositories\Repository;

class IndexCtrl extends SiteCtrl
{
    
    public function __construct(
        SliderRepository $sliderRep,                 /* Здесь используется метод внедрения зависимостей. */
        PortfolioRepository $portfolioRep,
        ArticleRepository $articleRep
        ){ 
        /* 
            Здесь используется метод внедрения зависимостей.
            Т.е. при загрузке проекта создасться объект типа MenuRepository.
        */
        parent::__construct(
            new \Corp\Repositories\MenuRepository(new \Corp\Menu)
        );
        
        $this->s_rep = $sliderRep; 
        $this->p_rep = $portfolioRep; 
        $this->a_rep = $articleRep; 

        /* На главной странице есть правый siteBar */
        $this->bar = 'right';

        /* Здесь формируется строка 'pink.index' - это путь к шаблону */
        $this->teamplate = config('settings.theme').'.index';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()    
    {  
        $this->keywords = 'Home Page';
		$this->meta_desc = 'Home Page';
		$this->title = 'Home Page';      
        /* 
            Здесь формируется слайдер. 
            Сделано через переменную $sliders для того чтобы не подключать слайдер в каждем шаблоне (@include('slider')).            
        */
        $sliderItems = $this->getSliders();        
        $sliders = view(config('settings.theme').'.slider')->with('sliders', $sliderItems)->render();
        $this->vars['sliders'] = $sliders;

        /*
            Здесь формируется контент главной страницы, т.е. работы.
        */
        $portfolio = $this->getPortfolio();

        /*
            Здесь формируется правая колонка главной страницы.
        */
        $articles = $this->getArticles();
        $this->contentRightBar = view(config('settings.theme').'.indexBar')->with('articles', $articles)->render();

        $content = view(config('settings.theme').'.content')->with('portfolio', $portfolio)->render();
        $this->vars['content'] = $content;


        /* Метод родительского класса. Генерирует на базе шаблона страницу */
        return $this->renderOutput();
    }

    public function getSliders(){
        $sliders = $this->s_rep->get();      

        if($sliders->isEmpty()){
            return false;
        }
        /*
            Здесь с помощью метода transform() изменяется коллекция $sliders.
            Для каждой ячейки коллекции вызывается ананимная ф-ция, в которой меняется член коллекции - объект типа SliderRepository.
            Это делается для того чтобы изменить путь к изображения добавив к пути папку где распологаются изображения.
            Сделано так сложно для того чтобы при изменения пути к изображению можно было легко поправить код не меняя шаблон.
            При изменения пути, необходимо просто поправить файл "config/settings.php".
            Если в БД поле img="picture.jpeg", то после выполнения этой ф-ции img="slider-cycle/picture.jpeg"
        */
        $sliders->transform(function($item, $key){
            $item->img = Config::get('settings.slider_path').'/'.$item->img;
            return $item;
        });        
        // dd( $sliders);

        return $sliders;
    }

    protected function getPortfolio(){

        $countPort = Config::get('settings.home_port_count');   /* Получить значение 'home_port_count' из cofig/settings.php */
        $portfolio = $this->p_rep->get(
            '*', 
            $countPort, 
            false, 
            false, 
            ['fieldName'=>'created_at', 'sortDir'=>'desc']
        );

        return $portfolio;
    }

    protected function getArticles(){

        $countArticl = Config::get('settings.home_articles_count');   /* Получить значение 'home_articles_count' из cofig/settings.php */
        $articles = $this->a_rep->get(
            ['title', 'img', 'alias', 'created_at'],
             $countArticl, 
             false, 
             false, 
             ['fieldName'=>'created_at', 'sortDir'=>'desc']
            );

        return $articles;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
