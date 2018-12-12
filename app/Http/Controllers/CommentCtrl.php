<?php

namespace Corp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Validator;
use Auth;
use Corp\Comment;
use Corp\Article;

class CommentCtrl extends SiteCtrl
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * Метод обробатывает ajax-запрос типа POST. Здесь происходит сохранение нового комментария в БД.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {         
        
        /* 
            Здесь формируется массив который будет сохранен в БД.
            У этого массива имена ячеек должны соотвествовать именам полей в БД,
            поэтому такой код.
        */
        $data = $request->except('_token', 'comment_post_ID', 'comment_parent');
        $data['article_id'] = $request->input('comment_post_ID');
        $data['parent_id'] = $request->input('comment_parent');

        /* Проверка данных */
        $rules = [
            'article_id'=>'integer|required',
            'parent_id'=>'integer|required',
            'text'=>'string|required',
        ];
        $message = [
            'required'=>'Поле :attribute обязательно к заполнению',
            'email'=>'Поле :attribute должно соответствовать email-адрессу',
            'integer'=>'Поле :attribute должно быть integer'
        ];
        $validator = Validator::make($data, $rules, $message);

        /*
            Дополнительный набор проверок. 
            Если коментарий оставил аутентифицированный пользователь метод Auth::check() вернет true,
            т.к. стоит знак ! (отрицания) то return вернет false,
            тогда в проверку добавятся поля 'name' и 'email' с правилами 'required|max:255'.
            Т.е. проверять поля 'name' и 'email' если пользователь не аутентифицированный.
        */        
        $validator->sometimes(['name', 'email'], 'required|max:255', function($input){

            return !Auth::check();

        });

        /* Если произошла ошибка валидации, отправить объект ошибки клиенту. */
        if($validator->fails()){
            return \Response::json(['error'=>$validator->errors()->all()]);
        }
        

        /* СОХРАНЕНИЕ ДАННЫХ В БД */        
        $comment = new Comment($data);

        /* Есть ли аутентифицированный пользователь */
        $user = Auth::user();


        if($user){
            $comment->user_id = $user->id;
        }
        /* Связывание записей таблиц 'comments' и 'articles', т.е. статьи и коментария к ней. */
        $post = Article::find($data['article_id']);
        $post->comments()->save($comment);

        /* ФОРМИРОВАНИЕ ОТВЕТА */
        $comment->load('user');

        $data['id'] = $comment->id;        

        $data['email'] = (!empty($data['email'])) ? $data['email'] : $comment->user->email;
        $data['name'] = (!empty($data['name'])) ? $data['name'] : $comment->user->name;

        $data['hash'] = md5($data['email']);         /* Необходимо для аватарки. См. граватар */

        $view_comment = view(config('settings.theme').'.content_one_comment')->with('data', $data)->render();

        return \Response::json([
            'success'=>true, 
            'comment'=>$view_comment,
            'data'=>$data
            ]);

        exit();
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
