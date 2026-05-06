<?php
namespace Middleware;

use Models\Model;
use Models\User;
use Pecee\Http\Middleware\IMiddleware;
use Pecee\Http\Request;
use Services\Cache;

class UserInit implements IMiddleware {

    public function handle(Request $request)  : void {

    }

}