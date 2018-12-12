<?php

namespace Corp\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Corp\Http\Controllers\Controller;

use Corp\Http\Requests\ArticleRequest;
use Corp\Repositories\ArticleRepository;
use Gate;
use Corp\Article;
use Corp\Category;

/* Контроллер обрабатует маршруты по работе со статьми в админ панели. */

class ArticlesCtrl extends AdminCtrl
{
    
    public function __construct(ArticleRepository $a_rep){

        parent::__construct();
        
        $this->a_rep = $a_rep;
        $this->teamplate = config('settings.theme').'.admin.articles';
    }

    /**
     * Страница со всеми добавленными статьями (article) на сайт.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        
        /* Аутентифицирован ли пользователь. */ 
        $this->authUser();     
        /* 
            Есть ли у аутентифицированного пользователя привелегия (Permission) типа 'VIEW_ADMIN_ARTICLES',
            т.е. если у него право увидеть главную страницу по работе со статьями админ-панели.
        */
        if(Gate::denies('VIEW_ADMIN_ARTICLES')){
            abort(403);
        }
        
        $this->title = 'Менеджер статей';

        $articles = $this->getArticles(); 
        
        $this->content = view(config('settings.theme').'.admin.articles_content')->with('articles', $articles)->render();

        /* Метод родительского класса. Генерирует на базе шаблона страницу */
        return $this->renderOutput();        
    }

    public function getArticles(){
        return $this->a_rep->get();
    }

    /**
     * Страница создания новой статьи (article) на сайте.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(){  

        /*  Есть ли у аутентифицированного пользователя права на сохранение новой статьи в БД. */
        if(Gate::denies('save', new Article())){
            abort(403);
        }

        $this->title = 'Добавить новую статью';

        $categories = Category::select(['title','alias','parent_id','id'])->get();
        
        /* 
            Здесь формируется специальный массив категорий. 
            Этот массив будет использован для создания <select>
            с помощью расширения Html&Form.
            [
                'Блог' => [
                    'id' => 'Компьютеры',
                    'id' => 'Интересное',
                    'id' => 'Советы',
                    ]
            ]
        */
        $list = [];        
        foreach ($categories as $category) {
			if($category->parent_id == 0) {
				$list[$category->title] = [];
			}
			else {
				$list[$categories->where('id',$category->parent_id)->first()->title][$category->id] = $category->title;    
			}            
        }
        		
		$this->content = view(config('settings.theme').'.admin.articles_create_content')->with('categories', $list)->render();
		
		return $this->renderOutput();

    }

    /**
     * Метод обрабатывает запрос на сохранение новой статьи (article) в БД.
     * 
     * ArticleRequest - создан для удобства, 
     * в нем реализован механизмы проверки прав пользователя и праверки введенных данных. 
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response     * 
     */
    public function store(ArticleRequest $request){
              
		$result = $this->a_rep->addArticle($request);
		
		if(is_array($result) && !empty($result['error'])) {
			return back()->with($result);
		}
		
		return redirect('/admin')->with($result);
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
     * Метод выдающий страницу редактирования статьи.
     *
     * @param  string  $alias
     * @return \Illuminate\Http\Response
     */
    public function edit($alias){

        $article = Article::where('alias', $alias)->first(); 

        $this->title = 'Редактирование материала - '.$article->title;
        
        /*  Есть ли у аутентифицированного пользователя права на редактирования статьи. */
        if(Gate::denies('edit', new Article())){
            abort(403);
        } 
                        
        // Деккодирование JSON-строки в поле $article->img.
        if(
            is_string($article->img) &&                                /* Строка ли */
            is_object(json_decode($article->img)) &&                   /* JSON-объект ли */
            json_last_error() == 'JSON_ERROR_NONE'                     /* Были ли ошибки при последнем JSON-декадировании */
            ){
                
            $article->img = json_decode($article->img);
        }
        
        $categories = Category::select(['title','alias','parent_id','id'])->get();
        
        /* 
            Здесь формируется специальный массив категорий. 
            Этот массив будет использован для создания <select>
            с помощью расширения Html&Form.
            [
                'Блог' => [
                    'id' => 'Компьютеры',
                    'id' => 'Интересное',
                    'id' => 'Советы',
                    ]
            ]
        */
        $list = [];        
        foreach ($categories as $category) {
			if($category->parent_id == 0) {
				$list[$category->title] = [];
			}
			else {
				$list[$categories->where('id',$category->parent_id)->first()->title][$category->id] = $category->title;    
			}            
        }
        
        $this->content = view(config('settings.theme').'.admin.articles_create_content')
                            ->with([
                                'categories' => $list,
                                'article' => $article,
                                ])
                            ->render();

        /* Метод родительского класса. Генерирует на базе шаблона страницу */
        return $this->renderOutput(); 
    }

    /**
     * Сохранение отредактированной статьи.
     *
     * @param  \Corp\Request\ArticleRequest  $request
     * @param  string  $alias
     * @return \Illuminate\Http\Response
     */
    public function update(ArticleRequest $request, $alias){       

        $article = Article::where('alias', $alias)->first();
              
		$result = $this->a_rep->updateArticle($request, $article);
		
		if(is_array($result) && !empty($result['error'])) {
			return back()->with($result);
		}
		
		return redirect('/admin')->with($result);
    }

    /**
     * Удаление статьи.
     *
     * @param  string  $alias
     * @return \Illuminate\Http\Response
     */
    public function destroy($alias){

        $article = Article::where('alias', $alias)->first();

		$result = $this->a_rep->deleteArticle($article);
		
		if(is_array($result) && !empty($result['error'])) {
			return back()->with($result);
		}
		
		return redirect('/admin')->with($result);
    }
}
