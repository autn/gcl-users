<?php
namespace Gcl\GclUsers\Controllers;

use Auth;
use Route;
use Validator;
use Illuminate\Http\Request;
use Gcl\GclUsers\Models\Role;
use Gcl\GclUsers\Models\PermissionRoute;
use Gcl\GclUsers\Models\NodePermission;

class PermissionRouteController extends Controller
{
    /**
     * Create permission route action
     *
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request, $id = null)
    {
        // validate
        $validator = Validator::make($request->all(), [
            'route_name'    => 'required|max:255|string',
            'route_method'  => 'required|max:255|string'
        ]);

        if ($validator->fails()) {
            return response()->json(arrayView('gcl.gclusers::errors/validation', [
                'errors' => $validator->errors()
            ]), 400);
        }

        if (!NodePermission::find($id)) {
            return response()->json(null, 404);
        }

        // add permissions and roles for the route
        $routePermission = PermissionRoute::setRoutePermissionsRoles(
            $id,
            $request['route_name'],
            $request['route_method']
        );

        return response()->json(arrayView('gcl.gclusers::routePermission/read', [
            'routePermission' => $routePermission
        ]), 201);
    }

    /**
     * Delete route permission
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        // get route permission by id
        $permissionRoute = PermissionRoute::find($id);

        if (!$permissionRoute) {
            return response()->json(null, 404);
        }

        // delete route permission
        $deleteRoutePermission = $permissionRoute->delete();

        if (!$deleteRoutePermission) {
            return response()->json(null, 500); // @codeCoverageIgnore
        }

        return response()->json(null, 204);
    }

    /**
     * List route of a permission
     *
     * @param permission id
     * @return json
     */
    public function index($id = null)
    {
        $node = NodePermission::where('id', $id)->get();

        if (!$node->count()) {
            return response()->json(null, 404);
        }

        $routes = PermissionRoute::where('permission_id', $id)->get();

        $results = [];
        if ($routes->count()) {
            foreach ($routes as $route) {
                $route = array(
                    'id'           => $route->id,
                    'route_method' => $route->route_method,
                    'route_name'   => $route->route_name
                );
                $results[] = (object)$route;
            }
        }

        return response()->json(arrayView('gcl.gclusers::route/browse', [
            'routes' => $results,
        ]), 200);
    }

    /**
     * List all routes in app
     *
     * @param
     * @return Response
     */
    public function getAllRoutes()
    {
        $routes = Route::getRoutes();
        $results = [];

        if ($routes != null) {
            foreach ($routes as $route) {
                $route = [
                    'id'           => null,
                    'route_method' => is_array($method = $route->getMethods()) ? $method[0] : $method,
                    'route_name'   => $route->getPath() === '/' ? $route->getPath() : '/' . $route->getPath()
                ];
                $results[] = (object)$route;
            }
        }

        return response()->json(arrayView('gcl.gclusers::route/browse', [
            'routes' => $results,
        ]), 200);
    }

    /**
     * List all routes in app has not been added to permissions tree
     *
     * @param
     * @return Response
     */
    public function getAllRoutesNotTree()
    {
        // Get all routes
        $routes = Route::getRoutes();

        // Get all routes has been added to permissions tree
        $permissionOnTree = PermissionRoute::all()->toArray();

        $diff = (new PermissionRoute)->getRouteNotTree($routes, $permissionOnTree);

        return response()->json(arrayView('gcl.gclusers::route/browse', [
            'routes' => $diff,
        ]), 200);
    }
}
