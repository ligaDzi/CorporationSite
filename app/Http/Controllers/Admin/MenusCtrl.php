<?php

namespace Corp\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Corp\Http\Controllers\Controller;

use Corp\Repositories\MenuRepository;
use Corp\Repositories\ArticleRepository;
use Corp\Repositories\PortfolioRepository;
use Gate;
use Menu;
use Corp\Http\Requests\MenuRequest;

/* Контроллер обрабатует маршруты по работе с меню в админ панели. */
class MenusCtrl extends AdminCtrl
{
    protected $m_rep;
    
    public function __construct(MenuRepository $m_rep, ArticleRepository $a_rep, PortfolioRepository $p_rep)
    {
        parent::__construct();

        $this->m_rep = $m_rep;
        $this->a_rep = $a_rep;
        $this->p_rep = $p_rep;
        
        $this->teamplate = config('settings.theme').'.admin.menus';        
    }

    /**
     * Вывести все созданные пункты меню.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
                    
        /* Аутентифицирован ли пользователь. */ 
        $this->authUser();     
        /* 
            Есть ли у аутентифицированного пользователя привелегия (Permission) типа 'VIEW_ADMIN_MENU',
            т.е. если у него право увидеть главную страницу по работе с меню админ-панели.
        */    
        if(Gate::denies('VIEW_ADMIN_MENU')) {
			abort(403);	
        } 
          
        $this->title = 'Менеджер меню';

        $menu = $this->getMenus();
        
        $this->content = view(config('settings.theme').'.admin.menus_content')->with('menus',$menu)->render();
        
        return $this->renderOutput();
    }
    
    public function getMenus()
    {        
        $menu = $this->m_rep->get();
        
        if($menu->isEmpty()) {
			return false;
		}
		
		return Menu::make('forMenuPart', function($m) use($menu) {
			
			foreach($menu as $item) {
				if($item->parent == 0) {
					$m->add($item->title,$item->path)->id($item->id);
				}
				
				else {
					if($m->find($item->parent)) {
						$m->find($item->parent)->add($item->title,$item->path)->id($item->id);
					}
				}
			}			
		});
    }

    /**
     * Вывод страницы добавления нового пункта меню.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(){
            	
    	$this->title = 'Новый пункт меню';
        
        /**
         * roots() - метод расширения Menu, возвращает только родительские пункты меню. 
         */
    	$tmp = $this->getMenus()->roots();
    	
    	/**
         * reduce() - метод наподобие map() в JS, 
         * только кроме того что он пробегается по коллекции 
         * и для каждого элемента коллекции вызывает ф-цию,
         * он передает результат ф-ции в следующий вызов ф-ции 
         * для следующего элемента коллекции.
         * 
         * Здесь формируется массив с родительскими пунктоми меню.
         * Этот массив будет использован для создания в шаблоне выпадающего списка.
         */
    	$menus = $tmp->reduce(function($returnMenus, $menu) {
    		
    		$returnMenus[$menu->id] = $menu->title;
    		return $returnMenus;	
    		
        },['0' => 'Родительский пункт меню']);
        // dd($menus);
        
        /**
         * Здесь формируется массив с категориями.
         * Этот массив будет использован для создания в шаблоне выпадающего списка.* 
         */
    	$categories = \Corp\Category::select(['title','alias','parent_id','id'])->get();
    	
    	$list = [];
    	$list['0'] = 'Не используется';
    	$list['parent'] = 'Раздел блог';
    	
    	foreach($categories as $category) {
			if($category->parent_id == 0) {
				$list[$category->title] = array();
			}
			else {
				$list[$categories->where('id',$category->parent_id)->first()->title][$category->alias] = $category->title;
			}
        }
        // dd($list);
    	        
        /**
         * Здесь формируется массив со статьями.
         * Этот массив будет использован для создания в шаблоне выпадающего списка.* 
         */
    	$articles = $this->a_rep->get(['id','title','alias']);
    	
    	$articles = $articles->reduce(function ($returnArticles, $article) {
		    $returnArticles[$article->alias] = $article->title;
		    return $returnArticles;
		}, []);
		// dd($articles);
		    	        
        /**
         * Здесь формируется массив с фильтрами.
         * Этот массив будет использован для создания в шаблоне выпадающего списка.* 
         */
		$filters = \Corp\Filter::select('id','title','alias')->get()->reduce(function ($returnFilters, $filter) {
		    $returnFilters[$filter->alias] = $filter->title;
		    return $returnFilters;
		}, ['parent' => 'Раздел портфолио']);
        // dd($filters);
            	        
        /**
         * Здесь формируется массив с работами.
         * Этот массив будет использован для создания в шаблоне выпадающего списка.* 
         */
		$portfolios = $this->p_rep->get(['id','alias','title'])->reduce(function ($returnPortfolios, $portfolio) {
		    $returnPortfolios[$portfolio->alias] = $portfolio->title;
		    return $returnPortfolios;
		}, []);
        // dd($portfolios);
        
		$this->content = view(config('settings.theme').'.admin.menus_create_content')->with(['menus'=>$menus,'categories'=>$list,'articles'=>$articles,'filters' => $filters,'portfolios' => $portfolios])->render();	
				
		return $this->renderOutput();
    }

    /**
     * Сохранение нового пункта меню в БД.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(MenuRequest $request){
        
        $result = $this->m_rep->addMenu($request);
		
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
     * Редактирование пункта меню.
     *
     * Здесь используется метод внедрения зависимостей.
     * При вызаве edit() в его аргументы попадает id,
     * конкретного пункта меню,
     * но механизм внедрения зависимостей находит запись в БД по id
     * и возвращает её, edit(\Corp\Menu $menu).
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(\Corp\Menu $menu){
        
        //dd($menu);

        $this->title = 'Редактирование ссылки - '.$menu->title;
        
        $type = FALSE;
        $option = FALSE;
        
        /* 
            Здесь из ссылки (типа - http://corporate.loc/articles) мы получаем имя маршрута, 
            который обрабатывает данный запрос и имена параметров данного маршрута. 
        */
        $route = app('router')->getRoutes()->match(app('request')->create($menu->path));       
        
        $aliasRoute = $route->getName();
        $parameters = $route->parameters();
        
       // dump($aliasRoute);
       // dump($parameters);
        
        if($aliasRoute == 'articles.index' || $aliasRoute == 'articlesCat') {
			$type = 'blogLink';
			$option = isset($parameters['cat_alias']) ? $parameters['cat_alias'] : 'parent';
		}		
		else if($aliasRoute == 'articles.show') {
			$type = 'blogLink';
			$option = isset($parameters['alias']) ? $parameters['alias'] : '';
		
		}		
		else if($aliasRoute == 'portfolios') {
			$type = 'portfolioLink';
			$option = 'parent';
		
		}		
		else if($aliasRoute == 'portfolio.show') {
			$type = 'portfolioLink';
			$option = isset($parameters['alias']) ? $parameters['alias'] : '';
		
		}		
		else {
			$type = 'customLink';
        }
            	
    	//dd($type);
    	$tmp = $this->getMenus()->roots();
    	
    	//null
    	$menus = $tmp->reduce(function($returnMenus, $menu) {
    		
    		$returnMenus[$menu->id] = $menu->title;
    		return $returnMenus;	
    		
    	},['0' => 'Родительский пункт меню']);
    	
    	$categories = \Corp\Category::select(['title','alias','parent_id','id'])->get();
    	
    	$list = array();
    	$list = array_add($list,'0','Не используется');
    	$list = array_add($list,'parent','Раздел блог');
    	
    	foreach($categories as $category) {
			if($category->parent_id == 0) {
				$list[$category->title] = array();
			}
			else {
				$list[$categories->where('id',$category->parent_id)->first()->title][$category->alias] = $category->title;
			}
		}
    	
    	$articles = $this->a_rep->get(['id','title','alias']);
    	
    	$articles = $articles->reduce(function ($returnArticles, $article) {
		    $returnArticles[$article->alias] = $article->title;
		    return $returnArticles;
		}, []);
		
		
		$filters = \Corp\Filter::select('id','title','alias')->get()->reduce(function ($returnFilters, $filter) {
		    $returnFilters[$filter->alias] = $filter->title;
		    return $returnFilters;
		}, ['parent' => 'Раздел портфолио']);
		
		$portfolios = $this->p_rep->get(['id','alias','title'])->reduce(function ($returnPortfolios, $portfolio) {
		    $returnPortfolios[$portfolio->alias] = $portfolio->title;
		    return $returnPortfolios;
		}, []);
		
		$this->content = view(config('settings.theme').'.admin.menus_create_content')->with(['menu' => $menu,'type' => $type,'option' => $option,'menus'=>$menus,'categories'=>$list,'articles'=>$articles,'filters' => $filters,'portfolios' => $portfolios])->render();
		
		
		
		return $this->renderOutput();
    }

    /**
     * Сохранение отредактированного пункта меню в БД.
     *
     * Здесь используется метод внедрения зависимостей.
     * @param  \Illuminate\Http\Request  $request
     * @param  Corp\Menu $menu
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,  \Corp\Menu $menu){   

        $result = $this->m_rep->updateMenu($request,$menu);
		
		if(is_array($result) && !empty($result['error'])) {
			return back()->with($result);
		}
		
		return redirect('/admin')->with($result);
    }

    /**
     * Удаление пункта меню.
     * 
     * Здесь используется метод внедрения зависимостей.
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(\Corp\Menu $menu){
        
        $result = $this->m_rep->deleteMenu($menu);
		
		if(is_array($result) && !empty($result['error'])) {
			return back()->with($result);
		}
		
		return redirect('/admin')->with($result);
    }
}