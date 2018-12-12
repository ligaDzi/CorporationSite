<?php

namespace Corp\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Corp\Http\Controllers\Controller;

use Corp\Repositories\PermissionsRepository;
use Corp\Repositories\RolesRepository;
use Gate;


/* Контроллер обрабатует маршруты по работе с ролями и привелегиями (правами пользователей) в админ панели. */
class PermissionsCtrl extends AdminCtrl
{
        
    protected $per_rep;
    protected $rol_rep;

    public function __construct(PermissionsRepository $per_rep, RolesRepository $rol_rep){

        parent::__construct();
        
        $this->per_rep = $per_rep;
        $this->rol_rep = $rol_rep;
        
        $this->teamplate = config('settings.theme').'.admin.permissions';
    }

    /**
     * Выдает страницу с ролями и правами, на которой сразу можно производить изменения.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){        
                        
        /* Аутентифицирован ли пользователь. */ 
        $this->authUser();     
        /* 
            Есть ли у аутентифицированного пользователя привелегия (Permission) типа 'EDIT_USER',
            т.е. если у него право увидеть главную страницу по работе с ролями и привелегиями (правами) админ-панели.
        */
        if(Gate::denies('EDIT_USER')) {
			abort(403);
        }
        
        $this->title = 'Менеджер прав пользователей';

        $roles = $this->getRoles(); 
        $permissions = $this->getPermissions();  
     
        $this->content = view(config('settings.theme').'.admin.permissions_content')
                            ->with(['roles'=>$roles,'priv'=>$permissions])
                            ->render();      
        
        
        /* Метод родительского класса. Генерирует на базе шаблона страницу */
        return $this->renderOutput();  
    }

    public function getRoles(){
        return $this->rol_rep->get();
    }

    public function getPermissions(){
        return $this->per_rep->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Сохранение измененных записей в БД.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){
        
		$result = $this->per_rep->changePermissions($request);
		
		if(is_array($result) && !empty($result['error'])) {
			return back()->with($result);
		}
		
		return back()->with($result);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
