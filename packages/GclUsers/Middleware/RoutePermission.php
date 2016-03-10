<?php

namespace Gcl\GclUsers\Middleware;

use Closure;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Exceptions\JWTException;
use Zizaco\Entrust\EntrustFacade as Entrust;
use Gcl\GclUsers\Models\PermissionRoute as PermissionRouteModel;

class RoutePermission
{
    /**
     * The JWTAuth implementation.
     *
     * @var JWTAuth
     */
    protected $auth;

    /**
     * The router instance.
     *
     * @var \Illuminate\Routing\Router
     */
    protected $router;

    /**
     * Create a new filter instance.
     *
     * @param  JWTAuth  $auth
     * @return void
     */
    public function __construct(JWTAuth $auth, Router $router)
    {
        $this->auth = $auth;
        $this->router = $router;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $route['route_method'] = $this->router->current()->methods()[0];
        $route['route_name'] = '/' . $this->router->current()->uri();

        $isAllowGuest = PermissionRouteModel::isAllowGuest($route);

        if (!$isAllowGuest) {

            if (($user = $this->user($request)) === 401) {
                return response()->json(null, 401);
            }

            $isAllPermission = PermissionRouteModel::isAllPermission($user);

            if (!$isAllPermission) {
                if (!PermissionRouteModel::hasPermission($user, $route)) {
                    return response()->json(null, 403);
                }
            }
        }

        return $next($request);
    }

    /**
     * Get the currently authenticated user or null.
     *
     * @return Illuminate\Auth\UserInterface|null
     */
    protected function user($request)
    {
        if (!$token = $this->auth->setRequest($request)->getToken()) {
            return 401;
        }

        try {
            $user = $this->auth->authenticate($token);
        } catch (JWTException $e) {
            return 401; // @codeCoverageIgnore
        }

        if (!$user) {
            return 401; // @codeCoverageIgnore
        }

        return $user;
    }
}
