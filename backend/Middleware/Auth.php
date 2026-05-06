<?php
namespace Middleware;

use Pecee\Http\Middleware\IMiddleware;
use Pecee\Http\Request;

class Auth implements IMiddleware {

    public function validate(Request $request){

    }

    public function handle(Request $request) : void {
        if (!is_login()) {
            redirect(url('login'), 403);
            exit;
        }
    }

}