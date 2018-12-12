<?php

namespace Corp;

use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    protected $table = 'portfolio';   
    
    protected $fillable = [
        'title', 'text', 'customer', 'alias', 'img', 'keywords', 'meta_desc', '	filter_alias'
    ];

    /* 
        Связь "один ко многим". 
        'Corp\Filter' - с какой моделью связывается
        'filter_alias' - поле в таблице 'portfolio', которое является внешним ключем
        'alias' - поле в таблице 'filter', которое является внешним ключем
    */
    public function filter(){
        return $this->belongsTo('Corp\Filter', 'filter_alias', 'alias');
    }
}
