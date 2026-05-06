<?php
namespace Middleware;

use Pecee\Http\Middleware\IMiddleware;
use Pecee\Http\Request;

class Localization implements IMiddleware {

    public function handle(Request $request)  : void {
        if (!isset($_SESSION['locale'])) {
            $_SESSION['locale'] = 'vi'; // Default locale
        }
        
        // You could also detect browser language here if desired
    }

}
