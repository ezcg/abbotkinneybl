<?php

namespace App\Http\Middleware;

use Closure;
//use Illuminate\Support\Facades\App;

class HttpsProtocol {

    public function handle($request, Closure $next)
    {
//        $isSsl = $request->secure();
//        $isSslForwarded = $request->server('HTTP_X_FORWARDED_PROTO');// != 'https';
//        if ($request->server('HTTP_X_FORWARDED_PROTO') != 'https' /*&& App::environment() === 'production'*/) {
//            $url = $request->getRequestUri();
//            $tmp = redirect()->secure($url);
//            return $tmp;
//        }
//
//        return $next($request);

    }

}
