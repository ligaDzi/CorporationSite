<?php

namespace Corp\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Corp\Portfolio;

class PortfolioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(){
        
        /* Есть ли у пользователя право добавлять новую работу (portfolio) в БД. */
        return \Auth::user()->canDo('ADD_PORTFOLIO');
    }
        
    /**
     * Это метод проверяет уникален ли псевдоним (поле alias) для работы.
     * 
     * Проверка поля alias вынесена в этод метод, а не прописана в методе rules(),
     * т.к. проверку во первых надо осуществлять только тогда, когда это поле заполнил пользователь,
     * а не сгенерировал метод transliterate();
     * во вторых при проверки надо получить доступ к валидатору ($validator).
     * 
     * Метод getValidatorInstance() переопределяется нами, вообщето этот метод возвращает объект валидатора.
     */
    protected function getValidatorInstance(){

        $validator = parent::getValidatorInstance();
               
        $validator->sometimes('alias','unique:portfolio|max:255', function($input) {
            
             /**
              * Если маршрут редактирования работы, а не создание новой.
              * То правирять уникальный ли псевданими не надо. 
              */
             if($this->method() == 'PUT'){
                 
                 $alias = $this->route()->parameter('portfolio');
                 $portfolio = Portfolio::where('alias', $alias)->first();
 
                 return ($portfolio->alias !== $input->alias) && !empty($input->alias);
             }
             return !empty($input->alias);
            
        });
 
        return $validator;  
    }	
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title'=>'required|max:255',
            'text' => 'required'
        ];
    }
}
