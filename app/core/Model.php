<?php

namespace App\Core;

use App\Helpers\Helpers;

if( !class_exists('Model') ){
    class Model{

        protected $helpers;

        public function __construct(){
            $this->helpers = new Helpers;
        }
    }
}