jQuery(document).ready(function($){

    // Нумерация комментариев
    $('.commentlist li').each(function(i){

        $(this).find('div.commentNumber').text('#' + (i+1));
    });

    // Отправка нового коментария
    $('#commentform').on('click', '#submit', function(e){

        e.preventDefault();

        /* Здесь хрониться кнопка submit*/
        var comParent = $(this);

        $('.wrap_result')
            .css('color','green')
            .text('Сохранение коментария')
            .fadeIn(500, function(){

                var data = $('#commentform').serializeArray();
						
                $.ajax({
							
                    url:$('#commentform').attr('action'),
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },        /* Требование к защите */
                    data:data,
                    type:'POST',
                    datatype:'JSON',
                    success: function(html) {
                        if(html.error){
                            $('.wrap_result')
                                .css('color', 'red')
                                .append('<br /><strong>Ошибка: </strong>' + html.error.join('<br />'))
                                .delay(2000)
                                .fadeOut(500);
                        }
                        else if(html.success){                            
                            $('.wrap_result')
                                .append('<br /><strong>Сохранено!</strong>')
                                .delay(2000)
                                .fadeOut(500,function() {
														
                                    if(html.data.parent_id > 0) {

                                        /* Коментарий к коментарию, сразу после коментария на который он был добавлен */
                                        comParent.parents('div#respond').prev().after('<ul class="children">' + html.comment + '</ul>');
                                    }
                                    else{                                        
                                        /* Есть ли хотя бы один коментарий. Если есть то есть список  */
                                        if($('ol.commentlist').length !== 0){

                                            /* Коментарий к статье. */
                                            $('ol.commentlist').append(html.comment);
                                        }
                                        else{
                                                                                        
                                            /* Первый коментарий. */
                                            $('#respond').before('<ol class="commentlist group">' + html.comment + '</ol>');
                                        }
                                    }

                                    /* Закрыть форму добавления коментария */
                                    $('#cancel-comment-reply-link').click();
                                });
                        }
                    },
                    error:function(err) {                        
                        $('.wrap_result')
                            .css('color', 'red')
                            .append('<br /><strong>Ошибка: </strong>' + err)
                            .delay(2000)
                            .fadeOut(500,function() {                                    
                                /* Закрыть форму добавления коментария */
                                $('#cancel-comment-reply-link').click();
                            });
                    }
                    
                });

            });

    });

});