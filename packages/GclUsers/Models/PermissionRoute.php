<?php

namespace Gcl\GclUsers\Models;

use Illuminate\Database\Eloquent\Model;
use Gcl\GclUsers\Models\RoleUser;
use Gcl\GclUsers\Models\PermissionRole;

class PermissionRoute extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'permission_route';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['route_name', 'permission_id', 'route_method'];

    /**
     * No timestamps
     */
    public $timestamps = false;

    /**
     * Set route permissions and roles
     *
     * @param string
     * @param array
     */
    public static function setRoutePermissionsRoles($permission_id, $route_name, $route_method)
    {
        $routePermission = parent::firstOrNew([
            'permission_id' => $permission_id,
            'route_name'    => $route_name,
            'route_method'  => $route_method
        ]);

        $routePermission->save();

        return $routePermission;
    }

    /**
     * Check roles have all permission (root permission)
     *
     * @param  $user
     * @return boolean
     */
    public static function isAllPermission($user)
    {
        $userId = $user->id;

        $listRole = (new RoleUser)->getUserRole($userId);

        if (empty($listRole)) {
            return false;
        }

        foreach ($listRole as $role) {
            $permissionRoot = PermissionRole::where(['role_id' => $role, 'permission_id' => '1'])->first();

            if (!empty($permissionRoot) && $permissionRoot->status == 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check roles have a permission
     *
     * @param  $user
     *         $route
     * @return boolean
     */
    public static function hasPermission($user, array $route = [])
    {
        // Get param
        $userId = $user->id;
        $route_method = $route['route_method'];
        $route_name = $route['route_name'];

        // Get roles
        $listRole = (new RoleUser)->getUserRole($userId);

        if (empty($listRole)) {
            return false;
        }

        // Get permission
        $permissions = parent::where([
            'route_method' => $route_method,
            'route_name' => $route_name
        ])->lists('permission_id')->toArray();

        if (empty($permissions)) {
            return false;
        }
        // Get permission status
        $rolePerm = PermissionRole::whereIn('role_id', $listRole)->whereIn('permission_id', $permissions)->get();

        if (!$rolePerm->count()) {
            return false;
        }

        foreach ($rolePerm as $perm) {
            if ($perm->status == 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all routes has not been added to permissions tree
     *
     * @param  $allRoutes: all routes
     *         $allTrees: all routes has been added to permissions tree
     * @return array
     */
    public function getRouteNotTree($allRoutes, $allTrees)
    {
        $results = [];
        $routeTree = [];

        foreach ($allTrees as $perm) {
            $routeTree[] = [
                'route_method' => $perm['route_method'],
                'route_name'   => $perm['route_name']
            ];
        }

        if (!empty($allRoutes)) {
            foreach ($allRoutes as $route) {
                $route = [
                    'id' => null,
                    'route_method' => is_array($method = $route->getMethods()) ? $method[0] : $method,
                    'route_name'   => $route->getPath() === '/' ? $route->getPath() : '/' . $route->getPath()
                ];

                if (!$this->checkInArray($route, $routeTree)) {
                    $results[] = (object)$route;
                }
            }
        }

        return $results;
    }

    /**
     * Check if a route param is in routes array
     *
     * @param  $param
     *         $array
     * @return boolean
     */
    protected function checkInArray($param, $array)
    {
        foreach ($array as $value) {
            if ($param['route_method'] === $value['route_method'] && $param['route_name'] === $value['route_name']) {
                return true;
            }
        }

        return false;
    }
}
