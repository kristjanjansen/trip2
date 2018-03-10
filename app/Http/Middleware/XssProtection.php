<?php

namespace App\Http\Middleware;

use Closure;

class XssProtection
{
    public function handle($request, Closure $next)
    {
        if (! in_array(strtolower($request->method()), ['put', 'post', 'patch'])) {
            return $next($request);
        }

        $input = $request->except('body');

        array_walk_recursive($input, function (&$input) {
            $input = strip_tags($input);
        });

        $request->merge($input);

        if ($request->has('body') && ! is_array($request->input('body'))) {
            $user = auth()->user();
            $role = false;
            if ($user) {
                if ($user->hasRole('admin')) {
                    $role = true;
                }
            }

            if (! $role) {
                $request->merge(['body' => strip_tags($request->body, config('site.allowedtags'))]);
            } else {
                $request->merge(['body' => trim(preg_replace('/\s\s+/', ' ', str_replace("\n", '', $request->body)))]);
            }
        }

        return $next($request);
    }
}
