<?php

namespace Corp\Repositories;

use Corp\Role;

/* Класс для работы с моделью Role */
class RolesRepository extends Repository {
	
	
	public function __construct(Role $role) {
		$this->model = $role;
	}
	
}