<?php

namespace Gcl\GclUsers\Models;

use Illuminate\Database\Eloquent\Model;

class PermissionRole extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'permission_role';

    /**
     * No timestamps
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['permission_id', 'role_id', 'status'];

    /**
     * Get list permission of role
     *
     * @return json
     */
    public function getRolePermission($role_id, $tree)
    {
        $result = [];

        if (empty($tree)) {
            return $result;
        }

        $permSaved = $this->getRoleSavedPermission($role_id);

        foreach ($tree as $key => $item) {
            $result[$key]['id'] = (int) $item['id'];
            $result[$key]['name'] = $item['name'];
            $result[$key]['display_name'] = $item['display_name'];
            $result[$key]['status'] = $this->checkStatus($item['id'], $permSaved) ? 1 : 0;

            if (!empty($item['children'])) {
                $result[$key]['children'] = $this->getRolePermission($role_id, $item['children']);
            }
        }

        return array_values($result);
    }

    /**
     * Get list permission saved of user
     *
     * @return json
     */
    public function getRoleSavedPermission($role_id)
    {
        $lists = [];
        $permSaved = parent::where('role_id', $role_id)->get();

        if ($permSaved) {
            foreach ($permSaved as $perm) {
                $lists[$perm->permission_id] = $perm->status;
            }
        }

        return $lists;
    }


    /**
     * Check status of a permission
     *
     * @return boolean
     */
    public function checkStatus($id, $list)
    {
        if (isset($list[$id]) && $list[$id] === '1') {
            return true;
        }

        return false;
    }

    /**
     * Get permission Role by permission id and role id
     *
     * @return model PermissionRole
     */
    public static function getPermissionRole($permission_id, $role_id)
    {
        $permissionRole = parent::where([
            'permission_id' => $permission_id,
            'role_id' => $role_id
        ])->first();

        if (!empty($permissionRole)) {
            $permissionRole->permission_id = (int) $permissionRole->permission_id;
            $permissionRole->role_id       = (int) $permissionRole->role_id;
            $permissionRole->status        = (int) $permissionRole->status;
        }

        return $permissionRole;
    }
}
