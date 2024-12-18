<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class VerifyAccessToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try{

            $user = JWTAuth::parseToken()->authenticate();
            $token = JWTAuth::getPayload();

            //Check that the token has correct type
            if($token->get('token_type') !== 'access')
            {
                return response()->json(['error' => 'Refresh tokens are not allowed to access resources'], 403);
            }

            return $next($request);

        }catch(Exception $e)
        {
            return response()->json(['error' => 'Token error'], 401);
        }
        
    }
}
