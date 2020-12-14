<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use \Firebase\JWT\JWT;
use \Firebase\JWT\ExpiredException;


class JwtAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        $requestArray = $request->all();
        try{
            $jwt_decoded = JWT::decode($requestArray["client-token"], env('JWT_SECRET'), array('HS256'));
        }
        catch(\Firebase\JWT\ExpiredException $ex){
            return response(["message"=> "Client token expired, please register again."],401);
        }
        catch(\Exception $e){
            return response(["message"=> "Bad client token."],401);;
        }
        $input = $request->all();
        $input["device"] = $jwt_decoded;
        $request->replace($input);

        return $next($request);
    }
}
