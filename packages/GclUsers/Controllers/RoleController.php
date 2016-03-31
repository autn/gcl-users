<?php
namespace Gcl\GclUsers\Controllers;

use Validator;
use Illuminate\Http\Request;
use Gcl\GclUsers\Models\Role;

class RoleController extends Controller
{
    /**
     * Create role action
     *
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:255|unique:roles',
            'display_name' => 'string|max:255',
            'description'  => 'max:1000',
            'active'       => 'integer'
        ]);

        if ($validator->fails()) {
            return response()->json(arrayView('gcl.gclusers::errors/validation', [
                'errors' => $validator->errors()
            ]), 400);
        }

        $role = Role::create($request->all());

        return response()->json(arrayView('gcl.gclusers::role/read', [
            'role' => $role
        ]), 201);
    }

    /**
     * Update role action
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request, $id = null)
    {
        // validate data
        $validator = Validator::make($request->all(), [
            'name'         => 'sometimes|required|string|max:255|unique:roles,name,'.$id,
            'display_name' => 'string|max:255',
            'description'  => 'max:1000',
            'active'       => 'integer'
        ]);

        if ($validator->fails()) {
            return response()->json(arrayView('gcl.gclusers::errors/validation', [
                'errors' => $validator->errors()
            ]), 400);
        }

        // check role
        $role = Role::find($id);

        if (!$role) {
            return response()->json(null, 404);
        }

        // update role
        $updateRole = $role->update($request->all());

        if (!$updateRole) {
            return response()->json(null, 500); // @codeCoverageIgnore
        }

        return response()->json(arrayView('gcl.gclusers::role/read', [
            'role' => $role
        ]), 200);
    }

    /**
     * Delete role
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        // get role by id
        $role = Role::find($id);

        if (!$role) {
            return response()->json(null, 404);
        }

        // delete role
        $deleteRole = $role->delete();

        if (!$deleteRole) {
            return response()->json(null, 500); // @codeCoverageIgnore
        }

        return response()->json(null, 204);
    }

    /**
     * View role
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        // get role by id
        $role = Role::find($id);

        if (!$role) {
            return response()->json(null, 404);
        }

        return response()->json(arrayView('gcl.gclusers::role/read', [
            'role' => $role
        ]), 200);
    }

    /**
     * index
     * @return json
     */
    public function index(Request $request)
    {
        $roles = Role::browse([
            'order'     => [ $request->input('sort', 'id') => $request->input('direction', 'desc') ],
            'limit'     => ($limit = (int)$request->input('limit', 25)),
            'offset'    => ($request->input('page', 1) - 1) * $limit,
            'filters'   => $request->all()
        ]);

        return response()->json(arrayView('gcl.gclusers::role/browse', [
            'roles' => $roles,
        ]), 200);
    }

    /**
     * index
     * @param  int $id
     * @return json
     */
    public function indexByUser(Request $request, $id)
    {
        $user = \App\User::find($id);

        if (!$user) {
            return response()->json(null, 404);
        }

        $roles = Role::browseByUser([
            'order'     => [ $request->input('sort', 'name') => $request->input('direction', 'asc') ],
            'limit'     => ($limit = (int)$request->input('limit', 25)),
            'offset'    => ($request->input('page', 1) - 1) * $limit,
            'user'      => $user
        ]);

        return response()->json(arrayView('gcl.gclusers::role/browse', [
            'roles' => $roles,
        ]), 200);
    }
}
