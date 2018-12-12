@if($portfolio)
	<div id="content-page" class="content group">
	    <div class="hentry group">
	        <h2>Добавленные работы</h2>
	        <div class="short-table white">
	            <table style="width: 100%" cellspacing="0" cellpadding="0">
	                <thead>
	                    <tr>
	                        <th class="align-left">ID</th>
	                        <th>Заголовок</th>
	                        <th>Текст</th>
	                        <th>Изображение</th>
	                        <th>Фильтр</th>
	                        <th>Псевдоним</th>
	                        <th>Действие</th>
	                    </tr>
	                </thead>
	                <tbody>
	                    
						@foreach($portfolio as $item)
						<tr>
	                        <td class="align-left">{{$item->id}}</td>
	                        <td class="align-left">{!! Html::link(route('admin.portfolio.edit',['portfolio'=>$item->alias]),$item->title) !!}</td>
	                        <td class="align-left">{{str_limit($item->text,200)}}</td>
	                        <td>
								@if(isset($item->img->mini))
								{!! Html::image(asset(config('settings.theme')).'/images/projects/'.$item->img->mini) !!}
								@endif
							</td>
	                        <td>{{$item->filter->title}}</td>
	                        <td>{{$item->alias}}</td>
	                        <td>
							{!! Form::open(['url' => route('admin.portfolio.destroy',['portfolio'=>$item->alias]),'class'=>'form-horizontal','method'=>'POST']) !!}
							    {{ method_field('DELETE') }}
							    {!! Form::button('Удалить', ['class' => 'btn btn-french-5','type'=>'submit']) !!}
							{!! Form::close() !!}
							</td>
						 </tr>	
						@endforeach	
	                   
	                </tbody>
	            </table>
	        </div>
			
			{!! HTML::link(route('admin.portfolio.create'),'Добавить  работу',['class' => 'btn btn-the-salmon-dance-3']) !!}
            
	        
	    </div>
	    <!-- START COMMENTS -->
	    <div id="comments">
	    </div>
	    <!-- END COMMENTS -->
	</div>
    
@endif