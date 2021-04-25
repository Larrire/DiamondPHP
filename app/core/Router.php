<?php

namespace App\Core;
use Exception;

require 'app/config/config.php';

if( !class_exists('Router') ){

    class Router{

        private static $routes = [
            'GET' => [],
            'POST' => [],
            'PUT' => [],
            'DELETE' => []
        ];

        // http methods - register functions
        public static function get($route, $function){
            Router::register_route($route, $function, 'GET');
        }

        public static function post($route, $function){
            Router::register_route($route, $function, 'POST');
        }

        public static function put($route, $function){
            Router::register_route($route, $function, 'PUT');
        }

        public static function delete($route, $function){
            Router::register_route($route, $function, 'DELETE');
        }

        // register functiion
        private static function register_route($route, $action, $http_method){
            if( !isset( Router::$routes[$http_method][$route] ) ){
                $total_size = count(explode('/', $route));
                $params_number = preg_match_all('/{*}/', $route);

                Router::$routes[$http_method][$route] = array(
                    'total_size' => $total_size,
                    'params_number' => $params_number,
                );
                
                Router::$routes[$http_method][$route]['action'] = $action;
            } else {
                throw new Exception("Error - The route {$http_method} - {$route} has already been declared");
            }
        }

        // verify if a route exists
        public static function route_exists($route){
            $route_parts = explode('/', trim($route, '/'));
            $total_size = count($route_parts);

            $possible_routes = array_filter(Router::$routes[$_SERVER['REQUEST_METHOD']], function ($route, $key) use ($total_size) {
                return $route['total_size'] === $total_size;
            }, ARRAY_FILTER_USE_BOTH);

            asort($possible_routes);

            foreach( $possible_routes as $key => $possible_route ){
                $new_key = explode('/', $key);
                $new_key = array_slice($new_key, 0 , $possible_route['total_size']-$possible_route['params_number']);

                $new_route = explode('/', $route);
                $new_route = array_slice($new_route, 0 , $possible_route['total_size']-$possible_route['params_number']);

                if( $new_key === $new_route ){
                    if( $possible_route['params_number'] > 0 ){
                        $possible_route['params'] = array_slice($route_parts, - $possible_route['params_number'] );
                    } else {
                        $possible_route['params'] = [];
                    }
                    return $possible_route;
                }
            }
            return false;
        }

        // redirect
        public static function direct($uri){
            $route = Router::route_exists($uri);

            if( $route ){
                if( gettype($route['action']) !== 'object' ){
                    $route_action = explode('@', $route['action']);
                    $controller_name = $route_action[0];
                    $method_name = $route_action[1];

                    if( file_exists("app/modules/{$controller_name}/controllers/{$controller_name}.php") ){
                        require "app/modules/{$controller_name}/controllers/{$controller_name}.php";
                        $controller = new $controller_name;
                        if( $controller->method_exists($method_name) ){
                            $controller->$method_name(...$route['params']);
                        } else {
                            throw new Exception("Method {$method_name} not found", 1);
                        }
                    } else {
                        throw new Exception("Controller {$controller_name} not found", 1);
                    }
                } else {
                    $route['action'](...$route['params']);
                }
            } else {
                Router::error404();
            }
        }

        // page not found method
        public static function error404($msg = null){
            if( Router::route_exists('error404') ){
                Router::direct('error404');
            } else {
                $page404 = "
                    <html>
                    <head>
                        <title>Page not found</title>
                        <style>
                            *{
                                margin: 0;
                                padding: 0;
                                box-sizing: border-box;
                            }
                            body{
                                // background-color: #eee;
                                background: linear-gradient(to bottom right, darkgray, lightgray, darkgray);
                            }
                            div.msg{
                                display: flex;
                                flex-direction: column;
                                align-items: center;
                                justify-content: center;
                                width: 100vw;
                                height: 100vh;
                            }
                            div.msg h2{
                                color: #333;
                                font-family: Arial, Sans-serif;
                                margin-bottom: 15px;
                            }
                            div.msg p{
                                color: #333;
                                font-size: 20px;
                            }

                        </style>
                    </head>
                    <body>
                        <div class='msg'>
                            <h2>Error 404 - Page not found</h2>
                            <div>
                                <p>The requested url does not exists.</p>
                            </div>
                        </div>
                    </body>
                    </html>
                ";
                echo $page404;
            }
        }

        public static function getRoutes(){
            return Router::$routes;
        }

        public static function get_routes_by_modules($modules){
            foreach($modules as $module){
                $file = "app/modules/{$module}/routes/routes.php";
                if( file_exists($file) ){
                    require_once $file;
                }
            }
        }
    }
}