<?php

namespace Gcl\GclUsers\Models;

use Auth;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use Gcl\GclUsers\Models\RoleUser;
use Gcl\GclUsers\Models\PermissionRole;
use Gcl\GclUsers\Models\NodePermission;

trait UserTrait
{
    use EntrustUserTrait;

    public function can($permissions, $arguments = [])
    {
        // Get param
        $userId = Auth::user()->id;

        // Get roles
        $listRole = (new RoleUser)->getUserRole($userId);

        if (empty($listRole) || empty($permissions)) {
            return false;
        }

        if (!is_array($permissions)) {
            $permissions = [$permissions];
        }

        // Get list permissions id
        $listPermissions = NodePermission::whereIn('name', $permissions)->lists('id');

        // Get permission status
        $rolePerm = PermissionRole::whereIn('role_id', $listRole)->whereIn('permission_id', $listPermissions)->get();

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
}
