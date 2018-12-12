<?php

namespace Corp\Repositories;

use Corp\Slider;

/* Класс для работы с моделью Slider */
class SliderRepository extends Repository
{
    public function __construct(Slider $slider){
        
        $this->model = $slider;
    }
}
