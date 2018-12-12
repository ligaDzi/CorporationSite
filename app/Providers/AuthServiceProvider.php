<?php

namespace Corp\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

use Corp\Article;
use Corp\Policies\ArticlePolicy;
use Corp\Permission;
use Corp\Policies\PermissionPolicy;
use Corp\Menu;
use Corp\Policies\MenuPolicy;
use Corp\User;
use Corp\Policies\UserPolicy;
use Corp\Portfolio;
use Corp\Policies\PortfolioPolicy;


class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Article::class => ArticlePolicy::class,
        Permission::class => PermissionPolicy::class,
        Menu::class => MenuPolicy::class,
        User::class => UserPolicy::class,
        Portfolio::class => PortfolioPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        /* Правила проверки прав пользователя. */
        
        Gate::define('VIEW_ADMIN', function($user){  
            /* canDo() - возвращает true если у пользователя есть данное ('VIEW_ADMIN') право. */
            return $user->canDo('VIEW_ADMIN');
        });

        Gate::define('VIEW_ADMIN_ARTICLES', function($user){  
            /* canDo() - возвращает true если у пользователя есть данное ('VIEW_ADMIN_ARTICLES') право. */
            return $user->canDo('VIEW_ADMIN_ARTICLES');
        });

        Gate::define('EDIT_USER', function($user){  
            /* canDo() - возвращает true если у пользователя есть данное ('EDIT_USER') право. */
            return $user->canDo('EDIT_USER');
        });

        Gate::define('VIEW_ADMIN_MENU', function($user){  
            /* canDo() - возвращает true если у пользователя есть данное ('VIEW_ADMIN_MENU') право. */
            return $user->canDo('VIEW_ADMIN_MENU');
        });

        Gate::define('EDIT_MENU', function($user){  
            /* canDo() - возвращает true если у пользователя есть данное ('EDIT_MENU') право. */
            return $user->canDo('EDIT_MENU');
        });

        Gate::define('VIEW_ADMIN_USERS', function($user){  
            /* canDo() - возвращает true если у пользователя есть данное ('VIEW_ADMIN_USERS') право. */
            return $user->canDo('VIEW_ADMIN_USERS');
        });

        Gate::define('VIEW_ADMIN_ROLES', function($user){  
            /* canDo() - возвращает true если у пользователя есть данное ('VIEW_ADMIN_ROLES') право. */
            return $user->canDo('VIEW_ADMIN_ROLES');
        });

        Gate::define('VIEW_ADMIN_PORTFOLIO', function($user){  
            /* canDo() - возвращает true если у пользователя есть данное ('VIEW_ADMIN_PORTFOLIO') право. */
            return $user->canDo('VIEW_ADMIN_PORTFOLIO');
        });

    }
}
