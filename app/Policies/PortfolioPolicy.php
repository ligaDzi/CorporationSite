<?php

namespace Corp\Policies;

use Corp\User;
use Corp\Portfolio;
use Illuminate\Auth\Access\HandlesAuthorization;

class PortfolioPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    
    /* Есть ли у аутентифицированного пользователя права на сохранение новой работы в БД */
    public function save(User $user){
        return $user->canDo('ADD_PORTFOLIO');
    }
    
    /* Есть ли у аутентифицированного пользователя права на редактирования работы в БД */
    public function edit(User $user){
        return $user->canDo('EDIT_PORTFOLIO');
    }
    
    /* Есть ли у аутентифицированного пользователя права на сохранение отредактированной работы в БД */
    public function update(User $user){
        return $user->canDo('UPDATE_PORTFOLIO');
    }
    
    /* Есть ли у аутентифицированного пользователя права на удаление работы из БД */
    public function destroy(User $user){
        return $user->canDo('DELETE_PORTFOLIO');
    }
}
