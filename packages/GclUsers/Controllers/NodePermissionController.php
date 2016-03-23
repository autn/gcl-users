<?php

namespace Gcl\GclUsers\Controllers;

use Input;
use Auth;
use Validator;
use Illuminate\Http\Request;
use Gcl\GclUsers\Models\NodePermission;
use Gcl\GclUsers\Models\PermissionRole;
use Gcl\GclUsers\Models\Role;
use App\User as AppUser;
// use JWTAuth;
// use Tymon\JWTAuth\Exceptions\JWTException;
// use Illuminate\Foundation\Testing\WithoutMiddleware;

/**
 * Authenticate
 */
class NodePermissionController extends Controller
{
    /**
     * Create node action
     *
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:255',
            'display_name' => 'string|max:255',
            'description'  => 'string|max:255',
            'parent_id'    => 'integer'
        ]);

        if ($validator->fails()) {
            return response()->json(arrayView('gcl.gclusers::errors/validation', [
                'errors' => $validator->errors()
            ]), 400);
        }

        $tree = NodePermission::model()->add($request->all());

        if (!$tree) {
            return response()->json('Parent id is invalid.', 400);
        }

        return response()->json(arrayView('gcl.gclusers::nodePermission/read', [
            'node' => $tree
        ]), 201);
    }

    /**
     * Get list permission action
     *
     * @return Response
     */
    public function index()
    {
        $tree = NodePermission::model()->getRootChildren();

        return response()->json(arrayView('gcl.gclusers::nodePermission/read', [
            'node' => $tree
        ]), 200);
    }

    /**
     * Update permission info action
     *
     * @return Response
     */
    public function updateInfo(Request $request, $id = null)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:255',
            'display_name' => 'string|max:255',
            'description'  => 'string|max:255',
            'parent_id'    => 'integer'
        ]);

        if ($validator->fails()) {
            return response()->json(arrayView('gcl.gclusers::errors/validation', [
                'errors' => $validator->errors()
            ]), 400);
        }

        $node = NodePermission::find($id);

        if (!$node) {
            return response()->json(null, 404);
        }

        // update permission
        $update = $node->update($request->all());

        if (!$update) {
            return response()->json(null, 500); // @codeCoverageIgnore
        }

        return response()->json(arrayView('gcl.gclusers::nodePermission/read', [
            'node' => $node
        ]), 200);
    }

    /**
     * Delete permission action
     *
     * @return Response
     */
    public function destroy($id = null)
    {
        // get permission by id
        $node = NodePermission::find($id);

        if (!$node) {
            return response()->json(null, 404);
        }

        // delete permission
        $deletePermission = $node->delete();

        if (!$deletePermission) {
            return response()->json(null, 500); // @codeCoverageIgnore
        }

        return response()->json(null, 204);
    }

    /**
     * Update all node permission action
     *
     * @param Request
     * @return Response
     */
    public function updateTree(Request $request)
    {
        $tree = NodePermission::model()->tree($request->data);

        return response()->json(arrayView('gcl.gclusers::nodePermission/read', [
            'node' => $tree
        ]), 201);
    }

    /**
     * Set role permission action
     *
     * @param Request
     * @return Response
     */
    public function storePermToRole(Request $request, $id = null)
    {
        $validator = Validator::make($request->all(), [
            'permission_id' => 'required|integer',
            'status'        => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(arrayView('gcl.gclusers::errors/validation', [
                'errors' => $validator->errors()
            ]), 400);
        }

        $permissionRole = PermissionRole::firstOrNew(['permission_id' => $request->permission_id,
            'role_id' => $id]);

        $permissionRole->status = $request->status;

        if (!$permissionRole->save()) {
            return response()->json(null, 500); // @codeCoverageIgnore
        }

        $permissionRole = PermissionRole::getPermissionRole($request->permission_id, $id);

        return response()->json(arrayView('gcl.gclusers::nodePermission/read', [
            'node' => $permissionRole // Add json_encode if return string
        ]), 201);
    }

    /**
     * Get role permission action
     *
     * @param Request
     * @return Response
     */
    public function getRolePerm($id = null)
    {
        if (!Role::find($id)) {
            return response()->json(null, 404);
        }

        $roles = NodePermission::model()->rolePerm($id);

        return response()->json(arrayView('gcl.gclusers::nodePermission/read', [
            'node' => $roles // Add json_encode if return string
        ]), 200);
    }

    /**
     * Check role is have all permission action
     *
     * @param Request
     * @return Response
     */
    public function checkAllPerm($id = null)
    {
        if (!Role::find($id)) {
            return response()->json(null, 404);
        }

        $permissionRoot = PermissionRole::where(['role_id' => $id, 'permission_id' => 1])->first();

        if (!empty($permissionRoot) && $permissionRoot->status == 1) {
            $isAll = true;
        } else {
            $isAll = false;
        }

        $roles = [
            'id'    => (int)$id,
            'type'  => 'permissions',
            'isAll' => $isAll
        ];

        return response()->json(arrayView('gcl.gclusers::nodePermission/read', [
            'node' => $roles // Add json_encode if return string
        ]), 200);
    }
}
