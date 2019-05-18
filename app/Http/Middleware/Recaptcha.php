<?php namespace App\Http\Middleware;

use Closure;

class Recaptcha
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (config('app.env') === 'production') {

            if(is_null($request->cookie('sc'))) {

                return redirect()->to('/sc?' . http_build_query(['next' => $request->getUri()]));
            }

        }

        return $next($request);
    }
}