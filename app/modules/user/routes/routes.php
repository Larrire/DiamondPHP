<?php

use App\Core\Router;

Router::get('user/{name}', 'user@teste');

Router::get('user/{name}/config/{tipo}', 'user@config');

Router::post('user/info', 'user@info');