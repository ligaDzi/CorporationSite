<?php

/* Мои настройки */

return [

    'theme'=>env('THEME', 'default'),     /*   */
    'slider_path'=>'slider-cycle',     /* Имя папки в которой расположенны изображения для слайдера  */
    'home_port_count'=>5,              /* Сколько работ (portfolio) отображать на главной странице  */
    'home_articles_count'=>3,          /* Сколько статей (article) отображать на главной странице в правой колонке  */
    'pagenate'=>2,                     /* Сколько одновременно статей (article) отображать на странице Блог с построничной навигации  */
    'recent_comments'=>3,              /* Сколько коментариев (comments) отображать на странице Блог  */
    'recent_portfolio'=>3,             /* Сколько работ (portfolio) отображать на странице Блог  */
    'other_portfolio'=>10,             /* Сколько других работ, помимо основной, отображать на странице детального просмотра работы  */
    
    /**
     * Эти параметры используются при формировании новых изображений на сервере.
     */
    /* Для статей */
	'articles_img' => [
        'max' => ['width'=>816,'height'=>282],
        'mini' => ['width'=>55,'height'=>55]
        
        ],

    'image' => [
            'width'=>1024,
            'height'=>768
        ],	
     
    /* Для работ */
	'portfolio_img' => [
        'max' => ['width'=>770,'height'=>368],
        'mini' => ['width'=>175,'height'=>175]
        
        ],	

];