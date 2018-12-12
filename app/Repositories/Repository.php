<?php

namespace Corp\Repositories;

use Config;

/* Абстрактный базовый класс для работы с моделями */
abstract class Repository
{
    protected $model = false;

    public function get(

        $select = '*',                       /* Какие поля возвращать */
        $take = false,                       /* Сколько записей выбрать из БД */        
        $pagination = false,                 /* Количество записей на одной странице при использовании постраничной навигации */
        $where = false,                      /* Дополнительное условие выборки записей из БД */
        $orderBy = [                         /* Нужна ли сортировка и направление сортировки */
            'fieldName'=>false, 
            'sortDir'=>false
        ] 
        ){

        $builder = $this->model->select($select);

        /* Сколько выбрать записей из таблици */
        if($take){
            $builder->take($take);
        }

        /* Есть ли дополнительные условия выборки записей из БД */
        if($where){
            $builder->where($where[0], $where[1], $where[2]);
        }
        
        /* Использовать ли сортировку */
        if(
            $orderBy['fieldName'] && 
            ($orderBy['sortDir'] == 'asc' || $orderBy['sortDir'] == 'desc')
            ){

                $builder->orderBy($orderBy['fieldName'], $orderBy['sortDir']);
        }

        /* Использовать ли постраничную навегацию */
        if($pagination){
            return $this->check($builder->paginate(Config::get('settings.pagenate')));
        }

        return $this->check($builder->get());
    }

    protected function check($result){

        if($result->isEmpty()){
            return false;
        }
        /*
            Здесь с помощью метода transform() изменяется коллекция $result.
            В $result хрониться выбранная информация из БД.
            В БД, в различных таблицах, есть своиство "img", в котором хранитсяь JSON-объект с именами файлов изображений.
            С помощью метода transform() декадируестя JSON-объект в обычный объект.
        */
        $result->transform(function($item, $key){

            if(
                is_string($item->img) &&                                   /* Строка ли */
                is_object(json_decode($item->img)) &&                      /* JSON-объект ли */
                json_last_error() == 'JSON_ERROR_NONE'                     /* Были ли ошибки при последнем JSON-декадировании */
                ){
                    
                $item->img = json_decode($item->img);
            }
            
            return $item;
        });
        
        return $result;
    }

    /* Вернуть одну запись */
    public function one($alias, $attr = []){

        $result = $this->model->where('alias', '=', $alias)->first();


        return $result;
    }
    
    /**
     * Метод реализующий механизм Транслитерации,
     * т.е. кирилические буквы заменяются латинскими.
     * 
     * Используется для генерации уникальных псевдонимов (поля alias в БД),
     * с помощью них реализуется маршрутизация на сайте.
     */
	public function transliterate($string, $isPortfolio = false) {
		$str = mb_strtolower($string, 'UTF-8');
		
		$leter_array = array(
			'a' => 'а',
			'b' => 'б',
			'v' => 'в',
			'g' => 'г,ґ',
			'd' => 'д',
			'e' => 'е,є,э',
			'jo' => 'ё',
			'zh' => 'ж',
			'z' => 'з',
			'i' => 'и,і',
			'ji' => 'ї',
			'j' => 'й',
			'k' => 'к',
			'l' => 'л',
			'm' => 'м',
			'n' => 'н',
			'o' => 'о',
			'p' => 'п',
			'r' => 'р',
			's' => 'с',
			't' => 'т',
			'u' => 'у',
			'f' => 'ф',
			'kh' => 'х',
			'ts' => 'ц',
			'ch' => 'ч',
			'sh' => 'ш',
			'shch' => 'щ',
			'' => 'ъ',
			'y' => 'ы',
			'' => 'ь',
			'yu' => 'ю',
			'ya' => 'я',
		);
		
		foreach($leter_array as $leter => $kyr) {
			$kyr = explode(',',$kyr);
			
			$str = str_replace($kyr,$leter, $str);
			
		}
        
        if($isPortfolio){
            //  A-Za-z0-9
            $str = preg_replace('/(\s|[^A-Za-z0-9])+/','',$str);
        }
        else{
            //  A-Za-z0-9-
            $str = preg_replace('/(\s|[^A-Za-z0-9\-])+/','-',$str);
        }
		
		$str = trim($str,'-');
		
		return $str;
	}

}
