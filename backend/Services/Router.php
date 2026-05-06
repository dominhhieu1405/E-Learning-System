<?php
namespace Services;

use Exception;
use Pecee\Http\Request;
use Pecee\SimpleRouter\SimpleRouter;

class Router extends SimpleRouter {

    public static function start() : void {

        // change this to whatever makes sense in your project

        Router::group(['middleware' => [\Middleware\UserInit::class, \Middleware\Localization::class]], function (){
            $routeDir = ROOT_PATH. '/backend/Router';

            foreach (glob("$routeDir/*.php") as $filename)
            {
                if(file_exists($filename)){
                    include $filename;
                }
            }
        });

        //Router::get('/404', 'Page@notFound');
        //Router::get('/forbidden', 'Page@notFound');

        Router::error(function(Request $request, Exception $exception) {

            switch($exception->getCode()) {
//                 Page not found
                case 404:
                    Router::response()->redirect('/404');
                    //Router::response()->redirect('/');
                    break;
                // Forbidden
                case 403:
                    Router::response()->redirect('/404');
                    //Router::response()->redirect('/');
            }

            exit($exception->getMessage());
        });
        // change default namespace for all routes
        Router::setDefaultNamespace('\Controllers');


        // Do initial stuff
        parent::start();
    }

}

