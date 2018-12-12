<?php

namespace Corp\Policies;

use Corp\User;
use Corp\Article;
use Illuminate\Auth\Access\HandlesAuthorization;

class ArticlePolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct(){
        
    }

    /* Есть ли у аутентифицированного пользователя права на сохранение новой статьи в БД */
    public function save(User $user){
        return $user->canDo('ADD_ARTICLES');
    }

    /* Есть ли у аутентифицированного пользователя права на изменение статьи */
    public function edit(User $user){
        return $user->canDo('UPDATE_ARTICLES');
    }

    /* Есть ли у аутентифицированного пользователя права на удаление статьи */
    public function destroy(User $user, Article $article){

        /* Удалять можно только свои статьи */
        return ($user->canDo('DELETE_ARTICLES')) && ($user->id == $article->user_id);
    }
}
