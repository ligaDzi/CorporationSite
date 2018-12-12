<?php

namespace Corp\Http\Controllers;

use Illuminate\Http\Request;

use Mail;

class ContactsCtrl extends SiteCtrl
{
        
    public function __construct(){ 
        /* 
            Здесь используется метод внедрения зависимостей.
            Т.е. при загрузке проекта создасться объект типа MenuRepository.
        */
        parent::__construct(
            new \Corp\Repositories\MenuRepository(new \Corp\Menu)
        );
        
        /* На главной странице есть левый siteBar */
        $this->bar = 'left';

        /* Здесь формируется строка 'pink.contacts' - это путь к шаблону */
        $this->teamplate = config('settings.theme').'.contacts';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){ 
        
        /* 
            Отправка сообщения на почту.
            Этот код не работает, т.к. надо подключить SMTP-сервер, 
            но сначало его надо зарегистрировать, а для этого нужен домен.
        */        	 	
	 	if ($request->isMethod('post')) {
		    
			$messages = [
			    'required' => 'Поле :attribute Обязательно к заполнению',
			    'email'    => 'Поле :attribute должно содержать правильный email адрес',
			];
			
			 $this->validate($request, [
		        'name' => 'required|max:255',
		        'email' => 'required|email',
				'text' => 'required'
		    ]/*,$messages*/);
			
			$data = $request->all();
			
			$result = Mail::send(config('settings.theme').'.email', ['data' => $data], function ($m) use ($data) {
				$mail_admin = env('MAIL_ADMIN');
				
	            $m->from($data['email'], $data['name']);

	            $m->to($mail_admin, 'Mr. Admin')->subject('Question');
	        });
			
			if($result) {
				return redirect()->route('contacts')->with('status', 'Email is send');
			}
			
		}
	 	

		$this->title = 'Котакты'; 

        /*
            Здесь формируется левая колонка страницы Контакты.
        */
        $this->contentLeftBar = view(config('settings.theme').'.contact_bar')->render();

        $content = view(config('settings.theme').'.contact_content')->render();
        $this->vars['content'] = $content;
        
        $this->vars['rightBar'] = '<h2>TEST</h2>';

        /* Метод родительского класса. Генерирует на базе шаблона страницу */
        return $this->renderOutput();
    }
}
