<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::resource('/', 'IndexCtrl', [
    'only'=>['index'],
    'names'=>[
        'index'=>'home'
    ]
]);

Route::resource('portfolio', 'PortfolioCtrl', [
    'parameters'=>[
         /* 
            У ресурсов автоматически создаются маршруты с определенными автоматически сгенерированными именами, 
            а так же с определенными параметрами в маршрутах. 
            Какие имена выбираются можно посмотреть в документации.
            Здесь переименовывается автоматически сгенерированный параметр 'portfolio' на 'alias'.
        */
        'portfolio'=>'alias'                       
    ],
    'names'=>[
        'index'=>'portfolios'
    ]
]);

Route::resource('articles', 'ArticlesCtrl', [
    'parameters'=>[
         /* 
            У ресурсов автоматически создаются маршруты с определенными автоматически сгенерированными именами, 
            а так же с определенными параметрами в маршрутах. 
            Какие имена выбираются можно посмотреть в документации.
            Здесь переименовывается автоматически сгенерированный параметр 'article' на 'alias'.
        */
        'article'=>'alias'                       
    ]
]);

/* Маршрут обслуживающий категории */
Route::get('articles/cat/{cat_alias?}', 'ArticlesCtrl@index')->name('articlesCat')->where('cat_alias', '[\w]+');


Route::resource('comment', 'CommentCtrl', [
    'only'=>['store']
]);

/* Маршрут обслуживающий страницу кантакты */
Route::match(['get','post'],'/contacts', 'ContactsCtrl@index')->name('contacts');

/* 
    Т.к. нам не нужны страница регистрации и страница сброса пароля, мы не используем Route::auth(). 
    Мы в ручную создадим страницу аутентификации пользователя, для доступа к понели администратора.
    Все эти методы обрабатывающие маршруты описаны в трейте Illuminate\Foundation\Auth\AuthenticatesUsers,
    который подключается к контроллеру LoginController.
    Но метод showLoginForm() я переопределю в контроллере LoginController, а так же добавлю еще необходимый код.
*/
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login'); 
Route::post('login', 'Auth\LoginController@login')->name('login');
Route::get('logout', 'Auth\LoginController@logout')->name('logout');

//admin
Route::group(['prefix' => 'admin', 'middleware' => 'auth'], function(){

    //admin
    Route::get('/', 'Admin\IndexCtrl@index')->name('adminIndex');

    //admin/articles
    Route::resource('/articles', 'Admin\ArticlesCtrl', [
                
        'names'=>[
            'index'=>'admin.articles.index',
            'edit'=>'admin.articles.edit',
            'create'=>'admin.articles.create',
            'destroy'=>'admin.articles.destroy',
            'update'=>'admin.articles.update',
            'show'=>'admin.articles.show',
            'store'=>'admin.articles.store'
        ]
    ]);

    //admin/portfolio
    Route::resource('/portfolio', 'Admin\PortfolioCtrl', [
                
        'names'=>[
            'index'=>'admin.portfolio.index',
            'edit'=>'admin.portfolio.edit',
            'create'=>'admin.portfolio.create',
            'destroy'=>'admin.portfolio.destroy',
            'update'=>'admin.portfolio.update',
            'show'=>'admin.portfolio.show',
            'store'=>'admin.portfolio.store'
        ]
    ]);

    //admin/permissions
    Route::resource('/permissions', 'Admin\PermissionsCtrl', [
                
        'names'=>[
            'index'=>'admin.permissions.index',
            'edit'=>'admin.permissions.edit',
            'create'=>'admin.permissions.create',
            'destroy'=>'admin.permissions.destroy',
            'update'=>'admin.permissions.update',
            'show'=>'admin.permissions.show',
            'store'=>'admin.permissions.store'
        ]
    ]);

    //admin/menus
    Route::resource('/menus', 'Admin\MenusCtrl', [
                
        'names'=>[
            'index'=>'admin.menus.index',
            'edit'=>'admin.menus.edit',
            'create'=>'admin.menus.create',
            'destroy'=>'admin.menus.destroy',
            'update'=>'admin.menus.update',
            'show'=>'admin.menus.show',
            'store'=>'admin.menus.store'
        ]
    ]);

    //admin/users
    Route::resource('/users', 'Admin\UsersCtrl', [
                
        'names'=>[
            'index'=>'admin.users.index',
            'edit'=>'admin.users.edit',
            'create'=>'admin.users.create',
            'destroy'=>'admin.users.destroy',
            'update'=>'admin.users.update',
            'show'=>'admin.users.show',
            'store'=>'admin.users.store'
        ]
    ]);
});