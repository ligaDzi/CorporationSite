<?php

namespace Corp;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';
    
    public function users(){
        return $this->belongsToMany('Corp\User', 'role_user');
    }
    
    public function permissions(){
        return $this->belongsToMany('Corp\Permission', 'permission_role');
    }
        
    /**
     * Есть ли у роли переданная привелегия.
     */
	public function hasPermission($name, $require = false)
    {
        if (is_array($name)) {
            foreach ($name as $permissionName) {
                $hasPermission = $this->hasPermission($permissionName);

                if ($hasPermission && !$require) {
                    return true;
                } elseif (!$hasPermission && $require) {
                    return false;
                }
            }
            return $require;
        } else {
            foreach ($this->permissions as $permission) {
                if ($permission->name == $name) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     *  Сохранение ролей и привелегий в БД.
     */        
    public function savePermissions($inputPermissions) {
		/**
         * В $inputPermissions содержится массив с id привелегий которые привязываются к кокретной роли.
         * sync() - реализует синхронизацию ролей и привелегий, 
         * по сути он заполняет связующую таблицу 'permission_role'.
         * 
         * В эту таблицу он добавляет id привелегий из $inputPermissions 
         * и добавляет id роли, для которой он был вызван.
         * 
         * Если же в $inputPermissions пустой массив,
         * то метод detach() удаляет все записи с id роли, для которой он был вызваню
         */
		if(!empty($inputPermissions)) {

			$this->permissions()->sync($inputPermissions);
		}
		else {
			$this->permissions()->detach();
		}
		
		return true;
	}
}

