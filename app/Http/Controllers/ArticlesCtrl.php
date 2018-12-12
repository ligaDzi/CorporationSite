<?php

namespace Corp\Http\Controllers;

use Illuminate\Http\Request;

use Config;
use Corp\Category;
use Corp\Repositories\PortfolioRepository;
use Corp\Repositories\ArticleRepository;
use Corp\Repositories\CommentRepository;

class ArticlesCtrl extends SiteCtrl
{   
    /* Объект класса CommentRepository */  
    protected $c_rep;

    public function __construct(                
        PortfolioRepository $portfolioRep,              /* Здесь используется метод внедрения зависимостей. */
        ArticleRepository $articleRep,
        CommentRepository $commentRep
        ){ 
        /* 
            Здесь используется метод внедрения зависимостей.
            Т.е. при загрузке проекта создасться объект типа MenuRepository.
        */
        parent::__construct(
            new \Corp\Repositories\MenuRepository(new \Corp\Menu)
        );
         
        $this->p_rep = $portfolioRep; 
        $this->a_rep = $articleRep; 
        $this->c_rep = $commentRep; 

        /* На странице Блог есть правый siteBar */
        $this->bar = 'right';

        /* Здесь формируется строка 'pink.articles' - это путь к шаблону */
        $this->teamplate = config('settings.theme').'.articles';
    }

    /**
     * Отображение всех статей.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($cat_alias = false)    /* $cat_alias устанавливается когда запрашивается страница конкретной категории  */
    {  
        $this->title = 'Блог';
        $this->keywords = 'Ключивые слова';
        $this->meta_desc = 'Краткое описание';


        /* Контент */
        $articles = $this->getArticles($cat_alias);
        $content = view(config('settings.theme').'.articles_content')->with('articles', $articles)->render();
        $this->vars['content'] = $content;

        /* Если запрашивается страница конкретной категории, надо переопределить мета-теги */
        if( $articles && $cat_alias){
            
            $category = $articles[0]->category;
            $this->setMetaCategory($category);            
        }

        /* Правая колонка */
        $countPortfolio = config('settings.recent_portfolio');
        $portfolio = $this->getPortfolio($countPortfolio);

        $countComments = config('settings.recent_comments');
        $comments = $this->getComments($countComments);

        $this->contentRightBar = view(config('settings.theme').'.articlesBar')->
                                    with([
                                        'portfolio'=>$portfolio, 
                                        'comments'=>$comments
                                    ])->
                                    render();

        /* Метод родительского класса. Генерирует на базе шаблона страницу */
        return $this->renderOutput();
        
    }

    public function getArticles($alias = false){

        /* Взять из БД статьи конкретной категории */
        $where = false;
        if($alias){      
            $id = Category::select('id')->where('alias', '=', $alias)->first()->id;
            $where = ['category_id', '=', $id];
        }

        $articles = $this->a_rep->get(
            ['id', 'title', 'alias', 'img', 'created_at', 'desc', 'user_id', 'category_id', 'keywords', 'meta_desc'], 
            false, 
            true,                                           /* Использовать постраничную навигацию */
            $where,                                         /* Дополнительное условие выборки записей из БД */
            ['fieldName'=>'created_at', 'sortDir'=>'desc']
        );

        if($articles){
            $articles->load('user', 'category', 'comments'); /* Подгрузка информации из связанных таблиц, для уменьшения SQL-запросов */
        }

        return $articles;
    }

    public function getPortfolio($countPortfolio){

        $portfolio = $this->p_rep->get(
            ['title', 'text', 'alias', 'customer', 'img', 'filter_alias'],
            $countPortfolio,
            false,
            false,
            ['fieldName'=>'created_at', 'sortDir'=>'desc']
        );

        return $portfolio;
    }

    public function getComments($countComments){

        $comments = $this->c_rep->get(
            ['text', 'name', 'email', 'site', 'article_id', 'user_id'],
            $countComments,
            false,
            false,
            ['fieldName'=>'created_at', 'sortDir'=>'desc']
        );
        
        if($comments){
            $comments->load('article', 'user'); /* Подгрузка информации из связанных таблиц, для уменьшения SQL-запросов */
        }

        return $comments;
    }

    public function setMetaCategory($category){
        
        /* Если запрашивается страница конкретной категории, надо переопределить мета-теги */
        $this->title = $category->title;
        $this->keywords = $category->keywords;
        $this->meta_desc = $category->meta_desc;
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
     * Отображение конкретной статьи.
     *
     * @param  string  $alias
     * @return \Illuminate\Http\Response
     */
    public function show($alias = false)
    {
        /* КОНТЕНТ */
        $article = $this->a_rep->one($alias, ['comments'=>true]);
                
        // Деккодирование JSON-строки в поле $article->img.
        if(
            is_string($article->img) &&                                /* Строка ли */
            is_object(json_decode($article->img)) &&                   /* JSON-объект ли */
            json_last_error() == 'JSON_ERROR_NONE'                     /* Были ли ошибки при последнем JSON-декадировании */
            ){
                
            $article->img = json_decode($article->img);
        }

        /* У каждой статьи свой заголовок страницы, свои ключивые слова, своё краткое описание. */
        if( !isset($article->id) ){            
            $this->title = $article->title;
            $this->keywords = $article->keywords;
            $this->meta_desc = $article->meta_desc;
        }

        $content = view(config('settings.theme').'.article_content')->with('article', $article)->render();
        $this->vars['content'] = $content;
        
        /* ПРАВАЯ КОЛОНКА */
        $countPortfolio = config('settings.recent_portfolio');
        $portfolio = $this->getPortfolio($countPortfolio);

        $countComments = config('settings.recent_comments');
        $comments = $this->getComments($countComments);

        $this->contentRightBar = view(config('settings.theme').'.articlesBar')->
                                    with([
                                        'portfolio'=>$portfolio, 
                                        'comments'=>$comments
                                    ])->
                                    render();
        
        /* Метод родительского класса. Генерирует на базе шаблона страницу */
        return $this->renderOutput();
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
