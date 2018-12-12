<?php

namespace Corp;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'login',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function articles(){
        return $this->hasMany('Corp\Article');
    }

    public function comments(){
        return $this->hasMany('Corp\Comment');
    }

    public function roles(){
        return $this->belongsToMany('Corp\Role', 'role_user');
    }
    /* 
        canDo() - возвращает true если у пользователя есть право, имя которго было переданно в метод. 
        В $permission может быть как одно "право", так и массив "прав".

        Если $require=true, то canDo() вернет true только если,
        у пользователя есть все права из массива $permission.

        Если $require=false, то canDo() вернет true если,
        у пользователя есть хотя бы одно из прав переданного в массива $permission.
    */
    public function canDo($permission, $require = false){

        if(is_array($permission)){
            
            foreach ($permission as $permName) {

                /* Рекурсия */
                $permName = $this->canDo($permName);

                if($permName && !$require){
                    /* Если хотя бы одно право из списка есть у пользователя, при $require = false */
                    return true;
                }
                else if(!$permName && $require){
                    /* Если хотя бы одного права из списка нет у пользователя, при $require = true */
                    return false;                    
                } 
            }            
            /* Программа сюда дойдет только, если $require=true и у пользователя есть все права из массива $permission.*/
            return $require;                  
        }
        else{
            foreach ($this->roles as $role) {

                foreach ($role->permissions as $perm) {                    

                    /* 
                        str_is() - Определяет, соответствует ли строка маске. 
                        Т.е 'foo*'=='foobar'.
                        В принципе здесь можно задать такое условие if($permission==$perm->name){}.
                    */
                    if(str_is($permission, $perm->name)){
                        return true;
                    }
                }
            }
        }
    }

    	
    /*
        Привязан ли пользователь к определенной роли или списку ролей.
    */
	public function hasRole($name, $require = false)
    {
        if (is_array($name)) {
            foreach ($name as $roleName) {

                /* Рекурсия */
                $hasRole = $this->hasRole($roleName);

                if ($hasRole && !$require) {
                    /* Если хотя бы одна роль из списка есть у пользователя, при $require = false */
                    return true;
                } elseif (!$hasRole && $require) {
                    /* Если хотя бы одной роли из списка нет у пользователя, при $require = true */
                    return false;
                }
            }
            /* Программа сюда дойдет только, если $require=true и у пользователя есть все роли из массива $name.*/
            return $require;
        } else {
            foreach ($this->roles as $role) {
                if ($role->name == $name) {
                    return true;
                }
            }
        }

        return false;
    }
    
}
