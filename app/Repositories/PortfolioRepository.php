<?php

namespace Corp\Repositories;

use Corp\Portfolio;
use Corp\Filter;
use Gate;
use Image;
use Config;

/* Класс для работы с моделью Portfolio */
class PortfolioRepository extends Repository
{
    public function __construct(Portfolio $portfolio){
        
        $this->model = $portfolio;        
    }

    public function one($alias, $attr = []){

        $portfolio = parent::one($alias, $attr);

        // Деккодирование JSON-строки в поле $portfolio->img.
        if(
            is_string($portfolio->img) &&                                /* Строка ли */
            is_object(json_decode($portfolio->img)) &&                   /* JSON-объект ли */
            json_last_error() == 'JSON_ERROR_NONE'                     /* Были ли ошибки при последнем JSON-декадировании */
            ){
                
            $portfolio->img = json_decode($portfolio->img);
        }
        return $portfolio;
    }
        
    /**
     * Метод сохранения новой работы в БД.
     */
	public function addPortfolio($request) {
        

		if(Gate::denies('save', $this->model)) {
			abort(403);
		}
		
		$data = $request->except('_token','image');
		
		if(empty($data)) {
			return array('error' => 'Нет данных');
		}
        
        /**
         * Если пользователь не ввел псевданим для работы, то его надо сгенерировать. 
         * Для этого здесь используется метод Транслитерации,
         * т.е. кирилические буквы заменяются латинскими.
         * Механизм Транслитерации описан в методе transliterate().        
         * 
         * Псевданим портфолио не может иметь знак "-", поэтому в метод transliterate()
         * передается true.
         */
		if(empty($data['alias'])) {
			$data['alias'] = $this->transliterate($data['title'], true);
        }       

        
        /**
         * Уникален ли псевданим для работы.
         */
        $result = $this->model->where('alias', '=', $data['alias'])->first();
        if($result){
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
                    ->save(public_path().'/'.config('settings.theme').'/images/projects/'.$obj->path); 
				
                $img->fit(
                    Config::get('settings.portfolio_img')['max']['width'], 
                    Config::get('settings.portfolio_img')['max']['height']
                    )
                    ->save(public_path().'/'.config('settings.theme').'/images/projects/'.$obj->max); 
				
				$img->fit(
                    Config::get('settings.portfolio_img')['mini']['width'],
                    Config::get('settings.portfolio_img')['mini']['height']
                    )
                    ->save(public_path().'/'.config('settings.theme').'/images/projects/'.$obj->mini); 			
                    
				$data['img'] = json_encode($obj);  
			}			
        }	
        else{
            return ['error' => 'Изображение не добавленно'];
        }

        /* Фильтер */
        $filter = Filter::select('alias')->where('id', '=', $data['filter_id'])->first();	
        
        $this->model->filter()->associate($filter);

        unset($data['filter_id']);

        
        if($this->model->fill($data)->save()) {
            return ['status' => 'Работа добавлен'];
        } 
    }
        
    /**
     * Сохранение отредактированной работы в БД.
     */
    public function updatePortfolio($request, $portfolio) {

		if(Gate::denies('update', $this->model)) {
			abort(403);
		}
		
		$data = $request->except('_token','image', '_method');
		
		if(empty($data)) {
			return array('error' => 'Нет данных');
		}
        
        /**
         * Если пользователь не ввел псевданим для работы, то его надо сгенерировать. 
         * Для этого здесь используется метод Транслитерации,
         * т.е. кирилические буквы заменяются латинскими.
         * Механизм Транслитерации описан в методе transliterate().   
         *      
         * Псевданим портфолио не может иметь знак "-", поэтому в метод transliterate()
         * передается true.
         */
		if(empty($data['alias'])) {
			$data['alias'] = $this->transliterate($data['title'], true);
        }        

        /**
         * Уникален ли псевданим для работы.
         */
        $result = $this->model->where('alias', '=', $data['alias'])->first();        

        if(isset($result) && ($result->id !== $portfolio->id)){
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
                    ->save(public_path().'/'.config('settings.theme').'/images/projects/'.$obj->path); 
				
                $img->fit(
                    Config::get('settings.portfolio_img')['max']['width'], 
                    Config::get('settings.portfolio_img')['max']['height']
                    )
                    ->save(public_path().'/'.config('settings.theme').'/images/projects/'.$obj->max); 
				
				$img->fit(
                    Config::get('settings.portfolio_img')['mini']['width'],
                    Config::get('settings.portfolio_img')['mini']['height']
                    )
                    ->save(public_path().'/'.config('settings.theme').'/images/projects/'.$obj->mini); 			
                    
				$data['img'] = json_encode($obj);  
			}			
        }	
        else{
            return ['error' => 'Изображение не добавленно'];
        }
        
        /* Фильтер */
        $filter = Filter::select('alias')->where('id', '=', $data['filter_id'])->first();	
        $portfolio->filter()->associate($filter);
                
        unset($data['filter_id']);	        
        
        $portfolio->fill($data); 
        
        if($portfolio->update()) {
            return ['status' => 'Работа обновлен'];
        } 
    }

    /**
     * Удаление работы из БД.
     */
    public function deletePortfolio($portfolio){
        
		if(Gate::denies('destroy', $portfolio)) {
			abort(403);
        }

        if($portfolio->delete()){
            return ['status' => 'Работа удалена'];
        }        
    }
}
