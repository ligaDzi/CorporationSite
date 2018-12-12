<?php

namespace Corp\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Corp\Http\Controllers\Controller;

use Gate;
use Auth;

/* Контроллер выдающий главную страницу панели администратора */
class IndexCtrl extends AdminCtrl
{
    public function __construct(){

        parent::__construct();  

        $this->teamplate = config('settings.theme').'.admin.index';
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){

        /* Аутентифицирован ли пользователь. */ 
        $this->authUser();   
        /* 
            Есть ли у аутентифицированного пользователя привелегия (Permission) типа 'VIEW_ADMIN',
            т.е. если у него право увидеть админ-панель.
        */
        if(Gate::denies('VIEW_ADMIN')){
            abort(403);
        }

        $this->title = 'Панель администратора';

        /* Метод родительского класса. Генерирует на базе шаблона страницу */
        return $this->renderOutput();
    }
}
