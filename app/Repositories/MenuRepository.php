<?php

namespace Corp\Repositories;

use Corp\Menu;
use Gate;

/* Класс для работы с моделью Menu */
class MenuRepository extends Repository
{
    public function __construct(Menu $menu){
        
        $this->model = $menu;
    }
    	
	public function addMenu($request) {

		if(Gate::denies('save', $this->model)) {
			abort(403);
		}
		
		$data = $request->only('type','title','parent');
		
		if(empty($data)) {
			return ['error'=>'Нет данных'];
		}
		
		if(empty($data['type'])) {
			return ['error'=>'Введите адресс ссылки'];
		}
		
		//dd($request->all());
        
        /**
         * Каой тип меню.
         */
		switch($data['type']) {
            
            /* Пользовательская ссылка */
			case 'customLink':
				$data['path'] = $request->input('custom_link');
            break;
            
            /* Статья (article) */			
			case 'blogLink' :
			
				if($request->input('category_alias')) {
					if($request->input('category_alias') == 'parent') {
						$data['path'] = route('articles.index');
					}
					else {
						$data['path'] = route('articlesCat',['cat_alias'=>$request->input('category_alias')]);
					}
				}
				
				else if($request->input('article_alias')) {
					$data['path'] = route('articles.show',['alias' => $request->input('article_alias')]);
				}
			
            break;
            
            /* Работа (portfolio) */			
			case 'portfolioLink' :
				if($request->input('filter_alias')) {
					if($request->input('filter_alias') == 'parent') {
						$data['path'] = route('portfolios');
					}
				}
				
				else if($request->input('portfolio_alias')) {
					$data['path'] = route('portfolio.show',['alias' => $request->input('portfolio_alias')]);
				}
            break;
            		
		}		

        /* Удаление лишних ячеек из массива. */
		unset($data['type']);
		
		if($this->model->fill($data)->save()) {
			return ['status'=>'Ссылка добавлена'];
		}		
    }
        
    /* Сохранение отредактированной статьи */
	public function updateMenu($request, $menu) {

		if(Gate::denies('update', $this->model)) {
			abort(403);
		}
		
		$data = $request->only('type','title','parent');
		
		if(empty($data)) {
			return ['error'=>'Нет данных'];
		}
				
		if(empty($data['type'])) {
			return ['error'=>'Введите адресс ссылки'];
        }
        
		//dd($request->all());
		
		switch($data['type']) {
			
			case 'customLink':
				$data['path'] = $request->input('custom_link');
			break;
			
			case 'blogLink' :
			
				if($request->input('category_alias')) {
					if($request->input('category_alias') == 'parent') {
						$data['path'] = route('articles.index');
					}
					else {
						$data['path'] = route('articlesCat',['cat_alias'=>$request->input('category_alias')]);
					}
				}
				
				else if($request->input('article_alias')) {
					$data['path'] = route('articles.show',['alias' => $request->input('article_alias')]);
				}
			
			break;
			
			case 'portfolioLink' :
				if($request->input('filter_alias')) {
					if($request->input('filter_alias') == 'parent') {
						$data['path'] = route('portfolios');
					}
				}
				
				else if($request->input('portfolio_alias')) {
					$data['path'] = route('portfolio.show',['alias' => $request->input('portfolio_alias')]);
				}
			break;
			
		}
		
		unset($data['type']);
		
		if($menu->fill($data)->update()) {
			return ['status'=>'Ссылка обновлена'];
		}
    }
    	
	public function deleteMenu($menu) {

		if(Gate::denies('delete', $this->model)) {
			abort(403);
		}
		
		if($menu->delete()) {
			return ['status'=>'Ссылка удалена'];
		}
	}
}
