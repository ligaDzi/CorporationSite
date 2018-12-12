<?php

namespace Corp;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu';
    
    protected $fillable = [
        'title', 'path','parent'
    ];
       
    /**
     * Здесь переопределяется родительский метод.
     * 
     * Сделано это потому что при удалении родительского пункта меню (записи в БД),
     * надо удалить все дочерние пункты меню (записи в БД).
     */
    public function delete(array $options = []) {
    	
        self::where('parent',$this->id)->delete();
        
		return parent::delete($options);
	}

}
