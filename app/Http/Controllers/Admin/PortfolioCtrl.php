<?php

namespace Corp\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Corp\Http\Controllers\Controller;

use Corp\Http\Requests\PortfolioRequest;
use Corp\Repositories\PortfolioRepository;
use Corp\Portfolio;
use Gate;

/* Контроллер обрабатует маршруты по работе с работами в админ панели. */

class PortfolioCtrl extends AdminCtrl
{

    public function __construct(PortfolioRepository $p_rep){

        parent::__construct();
        
        $this->p_rep = $p_rep;
        $this->teamplate = config('settings.theme').'.admin.portfolio';
    }

    /**
     * Страница со всеми добавленными работами (portfolio) на сайт.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){        
                
        /* Аутентифицирован ли пользователь. */ 
        $this->authUser();  

        if(Gate::denies('VIEW_ADMIN_PORTFOLIO')){
            abort(403);
        }        
                
        $this->title = 'Менеджер работ';

        $portfolio = $this->getPortfolio();
                
        $this->content = view(config('settings.theme').'.admin.portfolio_content')->with('portfolio', $portfolio)->render();

        /* Метод родительского класса. Генерирует на базе шаблона страницу */
        return $this->renderOutput();  
    }

    public function getPortfolio(){
        return $this->p_rep->get();
    }

    /**
     * Страница создания новой работы (portfolio) на сайте.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(){        
        
        /*  Есть ли у аутентифицированного пользователя права на сохранение новой работы в БД. */
        if(Gate::denies('save', new Portfolio())){
            abort(403);
        }
        $this->title = 'Добавить новую работу';
        		
		$filters = $this->getFilters()->reduce(function ($returnFilters, $filter) {
		    $returnFilters[$filter->id] = $filter->title;
		    return $returnFilters;
        }, []);
        		
		$this->content = view(config('settings.theme').'.admin.portfolio_create_content')->with('filters',$filters)->render();
        
        return $this->renderOutput();
    }
    	
	public function getFilters() {
		return \Corp\Filter::all();
    }

    /**
     * Метод обрабатывает запрос на сохранение новой работы (portfolio) в БД.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PortfolioRequest $request){
                      
        $result = $this->p_rep->addPortfolio($request);
        		
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
        
        $portfolio = Portfolio::where('alias', $alias)->first();
        
        $this->title = 'Редактирование работы - '.$portfolio->title;
        
        /*  Есть ли у аутентифицированного пользователя права на редактирования статьи. */
        if(Gate::denies('edit', new Portfolio())){
            abort(403);
        }
                                
        // Деккодирование JSON-строки в поле $portfolio->img.
        if(
            is_string($portfolio->img) &&                              /* Строка ли */
            is_object(json_decode($portfolio->img)) &&                 /* JSON-объект ли */
            json_last_error() == 'JSON_ERROR_NONE'                     /* Были ли ошибки при последнем JSON-декадировании */
            ){
                
            $portfolio->img = json_decode($portfolio->img);
        }
		
		$filters = $this->getFilters()->reduce(function ($returnFilters, $filter) {
		    $returnFilters[$filter->id] = $filter->title;
		    return $returnFilters;
        }, []);
                
        $this->content = view(config('settings.theme').'.admin.portfolio_create_content')
                            ->with([
                                'filters' => $filters,
                                'portfolio' => $portfolio,
                                ])
                            ->render();

        /* Метод родительского класса. Генерирует на базе шаблона страницу */
        return $this->renderOutput(); 
    }

    /**
     * Сохранение отредактированной статьи.
     *
     * @param  \Illuminate\Http\PortfolioRequest  $request
     * @param  string  $alias
     * @return \Illuminate\Http\Response
     */
    public function update(PortfolioRequest $request, $alias){
        
        $portfolio = Portfolio::where('alias', $alias)->first();
                      
		$result = $this->p_rep->updatePortfolio($request, $portfolio);
		
		if(is_array($result) && !empty($result['error'])) {
			return back()->with($result);
		}
		
		return redirect('/admin')->with($result);
    }

    /**
     * Удаление работы.
     *
     * @param  string  $alias
     * @return \Illuminate\Http\Response
     */
    public function destroy($alias){        
        
        $portfolio = Portfolio::where('alias', $alias)->first();

		$result = $this->p_rep->deletePortfolio($portfolio);
		
		if(is_array($result) && !empty($result['error'])) {
			return back()->with($result);
		}
		
		return redirect('/admin')->with($result);
    }
}
