<?php

namespace Corp\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Corp\Http\Controllers\Controller;

use Gate;
use Auth;
use Menu;
use Corp\User;

class AdminCtrl extends \Corp\Http\Controllers\Controller
{
    
    /* Объект класса PortfolioRepository */
    protected $p_rep;

    /* Объект класса ArticleRepository */
    protected $a_rep;

    /* Объект аутентифицированного пользователя */
    protected $user;

    /* Имя шаблона */
    protected $teamplate;
    
    /* Основная часть каждой страницы панели администратора */
    protected $content = false;
    
    /* Заголовок страницы */
    protected $title;
    
    /* Массив переменных передающихся в шаблон */
    protected $vars = [];
    
    public function __construct(){

        /* 
            Auth::user() - наченая с версии Laravel 5.2 в конструктарах нет доступа к фасаду Auth и к сессиям,
            связоно с тем, что необходимые файлы еще не загружены.
            В др. методах контроллера Auth::user() работает правильно.
        */
        // $this->user = Auth::user();
        // if(!$this->user){
        //     abort(403);
        // }
    }
    
    /* Метод генерирующий шаблон */
    public function renderOutput(){

        $this->vars['title'] = $this->title;

        $menu = $this->getMenu();
        $navigation = view(config('settings.theme').'.admin.navigation')->with('menu', $menu)->render();
        $this->vars['navigation'] = $navigation;
		
		if($this->content) {
			$this->vars['content'] = $this->content;
		}
		
        $footer = view(config('settings.theme').'.admin.footer')->render();
        $this->vars['footer'] = $footer;
        
        return view($this->teamplate)->with($this->vars);
    }
    
    /* Метод создающий мень для админ панели. Он использует установленное мной расширение Menu. */
    public function getMenu(){

        $mBuilder = Menu::make('adminMenu', function($menu){
            
            /* 'admin.articles.index' - это имя маршрута автоматически созданного для ресурса ArticlesCtrl. */
            $menu->add('Статьи', ['route' => 'admin.articles.index']); 
            
            if(Gate::allows('VIEW_ADMIN_PORTFOLIO')){                
                $menu->add('Портфолио', ['route' => 'admin.portfolio.index']);  
            }
            
            $menu->add('Меню', ['route' => 'admin.menus.index']); 
            
            if(Gate::allows('VIEW_ADMIN_USERS')){                
                $menu->add('Пользователи', ['route' => 'admin.users.index']); 
            }
            
            if(Gate::allows('VIEW_ADMIN_USERS')){                
                $menu->add('Привилегии', ['route' => 'admin.permissions.index']); 
            }           
             
        });        

        return $mBuilder;
    }

    /* 
        Аутентифицирован ли пользователь. 
        Этот метод должен вызываться первым в каждом методе обработчике маршрута 
        наследующим данный класс. 
    */
    public function authUser(){

        /* Auth::user() - возвращает аутентифицированного пользователя. */
        $this->user = Auth::user();       
        if(!$this->user){
            abort(403);
        }
    }
}
