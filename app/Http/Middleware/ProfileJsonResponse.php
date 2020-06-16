<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

class ProfileJsonResponse
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

        $response = $next($request);

        // check if debug bar is enabled
        if ( ! app()->bound('debugbar') || ! app('debugbar')->isEnabled() ) {
            return $response;
        }

        // Profile the JsonResponse
        if ($response instanceof JsonResponse && $request->has('_debug')) 
        {
            // dd('debuggin');
            // $response->setData(array_merge($response->getData(true), [
            //     'debugbar' => app('debugbar')->getData()
            // ]));
            $response->setData(array_merge([
                'debugbar' => Arr::only(app('debugbar')->getData(), 'queries')
            ], $response->getData(true)));
        }

        return $response;
    }
}
