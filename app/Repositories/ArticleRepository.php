<?php

namespace Corp\Repositories;

use Corp\Article;
use Gate;
use Image;
use Config;

/* Класс для работы с моделью Article */
class ArticleRepository extends Repository
{
    public function __construct(Article $article){
        
        $this->model = $article;
    }

    /* Вернуть одну запись */
    public function one($alias, $attr = []){

        $article = parent::one($alias, $attr);

        if($alias && !empty($attr)){
            /* 
                Здесь осуществляется подгрузка данных из связанных таблиц. 
                Так называемая жадная загрузка для уменьшения запросов к БД. 
            */
            $article->load('comments');
            $article->comments->load('user');
        }

        return $article;
    }
    
    /**
     * Метод сохранения новой статьи в БД.
     */
	public function addArticle($request) {

		if(Gate::denies('save', $this->model)) {
			abort(403);
		}
		
		$data = $request->except('_token','image');
		
		if(empty($data)) {
			return array('error' => 'Нет данных');
		}
        
        /**
         * Если пользователь не ввел псевданим для статьи, то его надо сгенерировать. 
         * Для этого здесь используется метод Транслитерации,
         * т.е. кирилические буквы заменяются латинскими.
         * Механизм Транслитерации описан в методе transliterate().        
         * 
         */
		if(empty($data['alias'])) {
			$data['alias'] = $this->transliterate($data['title']);
        }
        
        /**
         * Уникален ли псевданим для статьи.
         */
        if($this->one($data['alias'], false)){
			$request->merge(['alias' => $data['alias']]);
			$request->flash();			
			return ['error' => 'Данный псевдоним уже используется'];            
        }
        
        /**
         * Сохранение изображения на сервере.
         * 
         * На сервере изображение храниться в трех вариантах: mini, max, path (оригинал),
         * а в БД хрониться JSON-объект с именами этих изображений.
         * На сервер мы передаем одно изображение (path) и из него надо сделать еще два (mini, max).
         */
		if($request->hasFile('image')) {
			$image = $request->file('image');
            
            /* Изображение скопировалось на сервер без ошибок? */
			if($image->isValid()) {
				
				$str = str_random(8);
                
                /**
                 *  stdClass - это стандартный ларавеливский пустой класс, 
                 *  который можно наполнить своими своиствами и методами.
                 */
				$obj = new \stdClass;
				
				$obj->mini = $str.'_mini.jpg';
				$obj->max = $str.'_max.jpg';
				$obj->path = $str.'.jpg';
                
                /**
                 * Здесь используется расширение "Intervention Image" 
                 * для создания и изменения изображений на литу, 
                 * а так же для создания изображений нужного нам размера.
                 */
				$img = Image::make($image);
                
                /**
                 * fit() - изменяет размер изображения
                 */
				$img->fit(
                    Config::get('settings.image')['width'], 
                    Config::get('settings.image')['height']
                    )
                    ->save(public_path().'/'.config('settings.theme').'/images/articles/'.$obj->path); 
				
                $img->fit(
                    Config::get('settings.articles_img')['max']['width'], 
                    Config::get('settings.articles_img')['max']['height']
                    )
                    ->save(public_path().'/'.config('settings.theme').'/images/articles/'.$obj->max); 
				
				$img->fit(
                    Config::get('settings.articles_img')['mini']['width'],
                    Config::get('settings.articles_img')['mini']['height']
                    )
                    ->save(public_path().'/'.config('settings.theme').'/images/articles/'.$obj->mini); 			
                    
				$data['img'] = json_encode($obj);  
			}			
        }        	
        else{
            return ['error' => 'Изображение не добавленно'];
        }		
        
        $this->model->fill($data); 
        
        if($request->user()->articles()->save($this->model)) {
            return ['status' => 'Материал добавлен'];
        } 
    }
    
    /**
     * Сохранение отредактированной статьи в БД.
     */
    public function updateArticle($request, $article) {

		if(Gate::denies('edit', $this->model)) {
			abort(403);
		}
		
		$data = $request->except('_token','image', '_method');
		
		if(empty($data)) {
			return array('error' => 'Нет данных');
		}
        
        /**
         * Если пользователь не ввел псевданим для статьи, то его надо сгенерировать. 
         * Для этого здесь используется метод Транслитерации,
         * т.е. кирилические буквы заменяются латинскими.
         * Механизм Транслитерации описан в методе transliterate().        
         * 
         */
		if(empty($data['alias'])) {
			$data['alias'] = $this->transliterate($data['title']);
        }        

        /**
         * Уникален ли псевданим для статьи.
         */
        $result = $this->one($data['alias'], false);

        if(isset($result) && ($result->id !== $article->id)){
			$request->merge(['alias' => $data['alias']]);
			$request->flash();			
			return ['error' => 'Данный псевдоним уже используется'];            
        }
        
        /**
         * Сохранение изображения на сервере.
         * 
         * На сервере изображение храниться в трех вариантах: mini, max, path (оригинал),
         * а в БД хрониться JSON-объект с именами этих изображений.
         * На сервер мы передаем одно изображение (path) и из него надо сделать еще два (mini, max).
         */
		if($request->hasFile('image')) {
			$image = $request->file('image');
            
            /* Изображение скопировалось на сервер без ошибок? */
			if($image->isValid()) {
				
				$str = str_random(8);
                
                /**
                 *  stdClass - это стандартный ларавеливский пустой класс, 
                 *  который можно наполнить своими своиствами и методами.
                 */
				$obj = new \stdClass;
				
				$obj->mini = $str.'_mini.jpg';
				$obj->max = $str.'_max.jpg';
				$obj->path = $str.'.jpg';
                
                /**
                 * Здесь используется расширение "Intervention Image" 
                 * для создания и изменения изображений на литу, 
                 * а так же для создания изображений нужного нам размера.
                 */
				$img = Image::make($image);
                
                /**
                 * fit() - изменяет размер изображения
                 */
				$img->fit(
                    Config::get('settings.image')['width'], 
                    Config::get('settings.image')['height']
                    )
                    ->save(public_path().'/'.config('settings.theme').'/images/articles/'.$obj->path); 
				
                $img->fit(
                    Config::get('settings.articles_img')['max']['width'], 
                    Config::get('settings.articles_img')['max']['height']
                    )
                    ->save(public_path().'/'.config('settings.theme').'/images/articles/'.$obj->max); 
				
				$img->fit(
                    Config::get('settings.articles_img')['mini']['width'],
                    Config::get('settings.articles_img')['mini']['height']
                    )
                    ->save(public_path().'/'.config('settings.theme').'/images/articles/'.$obj->mini); 			
                    
				$data['img'] = json_encode($obj);  
			}			
		}		
        	
        else{
            return ['error' => 'Изображение не добавленно'];
        }
        
        $article->fill($data); 
        
        if($article->update()) {
            return ['status' => 'Материал обновлен'];
        } 
    }

    /**
     * Удаление статьи из БД.
     */
    public function deleteArticle($article){
        
		if(Gate::denies('destroy', $article)) {
			abort(403);
        }
        $article->comments()->delete();

        if($article->delete()){
            return ['status' => 'Материал удален'];
        }        
    }
}
