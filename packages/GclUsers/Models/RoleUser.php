<?php

namespace Gcl\GclUsers\Models;

use Zizaco\Entrust\EntrustRole;
use Illuminate\Database\Eloquent\Model;
use DB;

class RoleUser extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    // protected $table = 'role_user';

    /**
     * Get all roles of user
     *
     * @return roles id
     */
    public function getUserRole($userId)
    {
        $listRole = [];
        $roles = DB::table('role_user')->where('user_id', $userId)->lists('role_id');

        if ($roles){
            foreach ($roles as $role) {
                $listRole[] = $role;
            }
        }

        return $listRole;
    }
}
