<?php

namespace muhammadfahrul\crudbooster\middlewares;

use Closure;
use muhammadfahrul\crudbooster\helpers\CRUDBooster;

class CBAuthAPI
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if (env('CB_AUTH_API', true) == true) {
            CRUDBooster::authAPI();
        }

        return $next($request);
    }
}
