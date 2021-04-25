<?php

use App\Core\Router;

$modules = array('notes', 'user');

Router::get_routes_by_modules($modules);

Router::get('home', function(){
    echo 'teste';
});

// var_dump( Router::getRoutes() );
// Router::get('erro404', 'user@erro404');