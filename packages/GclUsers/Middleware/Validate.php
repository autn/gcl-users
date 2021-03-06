<?php

namespace Gcl\GclUsers\Middleware;

use Closure;
use Validator;

class Validate
{
    public function handle($request, Closure $next, $classValidate)
    {
        $classValidate::boot($request);
        $validator = Validator::make($request->all(), $classValidate::rules());

        if ($validator->fails()) {
            return response()->json(arrayView('gcl.gclusers::errors/validation', [
                'errors' => $validator->errors()
            ]), 400);
        }

        return $next($request);
    }
}
