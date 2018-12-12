<?php

namespace Corp\Http\Controllers;

use Illuminate\Http\Request;

use Config;
use Corp\Portfolio;
use Corp\Repositories\PortfolioRepository;

class PortfolioCtrl extends SiteCtrl
{
    /* Здесь используется метод внедрения зависимостей. */
    public function __construct(PortfolioRepository $portfolioRep){ 
        /* 
            Здесь используется метод внедрения зависимостей.
            Т.е. при загрузке проекта создасться объект типа MenuRepository.
        */
        parent::__construct(new \Corp\Repositories\MenuRepository(new \Corp\Menu));
         
        $this->p_rep = $portfolioRep; 

        /* Здесь формируется строка 'pink.portfolios' - это путь к шаблону */
        $this->teamplate = config('settings.theme').'.portfolios';
    }

    /**
     * Выводит страницу со всеми работами (portfolio).
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        
        $this->title = 'Работы';
        $this->keywords = 'Ключивые слова';
        $this->meta_desc = 'Краткое описание';


        /* Контент */
        $portfolios = $this->getPortfolios('*', false, true);
        $content = view(config('settings.theme').'.portfolios_content')->with('portfolios', $portfolios)->render();
        $this->vars['content'] = $content;

        /* Метод родительского класса. Генерирует на базе шаблона страницу */
        return $this->renderOutput();
    }

    public function getPortfolios(
        $select = '*', 
        $take = false, 
        $paginate = false, 
        $where = false, 
        $orderBy = ['fieldName'=>'created_at', 'sortDir'=>'desc']
        ){

        $portfolios = $this->p_rep->get($select, $take, $paginate, $where, $orderBy); 

        if($portfolios){
            $portfolios->load('filter');                /* Подгрузка информации из связанных таблиц, для уменьшения SQL-запросов */
        }


        return $portfolios;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(){
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){
        //
    }

    /**
     * Отображение конкретной работы.
     *
     * @param  string  $alias
     * @return \Illuminate\Http\Response
     */
    public function show($alias){

        /* КОНТЕНТ */
        $portfolio = $this->p_rep->one($alias);
                
        /* У каждой статьи свой заголовок страницы, свои ключивые слова, своё краткое описание. */
        $this->title = $portfolio->title;
        $this->keywords = $portfolio->keywords;
        $this->meta_desc = $portfolio->meta_desc;

        /* Другие работы ссылки на которые выводятся на странице */
        $countPortfolio = config('settings.other_portfolio');
        $portfolios = $this->getPortfolios('*', $countPortfolio, false);

        $content = view(config('settings.theme').'.portfolio_content')->with(['portfolio'=>$portfolio, 'portfolios'=>$portfolios])->render();
        $this->vars['content'] = $content;
        
        /* Метод родительского класса. Генерирует на базе шаблона страницу */
        return $this->renderOutput();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id){
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id){
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id){
        //
    }
}
