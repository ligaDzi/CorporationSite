<?php

namespace Corp\Http\Controllers\Auth;

use Corp\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /* Имя шаблона для входа в закрытый раздел сайта */   
    protected $loginView;

    /* 
        Переопределение стандартной аутентификации. 
        Т.е. вместо входа на сайт с помощью email, теперь вход будет осуществляться с помощью имени пользователя  username.
    */   
    protected $username = 'login';    
    
    /* После успешной аутентификации перенаправить пользователя по адрессу */
    protected $redirectTo = '/admin';    

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');

        /* Устанавливаю свое имя для шаблона входа в закрытый раздел сайта */
        $this->loginView = config('settings.theme').'.login';
    }
    
    /* Переопределяю метод из трейта AuthenticatesUsers */
    public function showLoginForm(){
            	
        $view = property_exists($this, 'loginView')
                    ? $this->loginView : '';

        if (view()->exists($view)) {
            return view($view)->with('title', 'Вход на сайт');
        }

        abort(404);
    }

    public function username()
    {
        /* Это я отредактировал. Было: return 'email'; */
        return 'login';
    }
}
