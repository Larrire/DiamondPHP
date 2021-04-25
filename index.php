<?php

use App\Core\Router;

require_once 'autoload.php';
require_once 'app/config/routes.php';

Router::direct(filter_input(INPUT_GET, 'url'));