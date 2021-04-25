<?php

namespace App\Core;

use App\Core\Template;
use App\Helpers\Helpers;

if( !class_exists('Controller') ){
    class Controller {

        protected $template;
        protected $helpers;

        public function __construct(){
            $this->template = new Template(get_class($this));
            $this->helpers = new Helpers;
        }

        public function method_exists($method_name){
            return method_exists(get_class($this), $method_name);
        }

        protected function redirect(){

        }

        protected function load_model(){
            
        }

    }
}