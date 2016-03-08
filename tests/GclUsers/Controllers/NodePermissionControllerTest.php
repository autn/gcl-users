<?php

use Gcl\GclUsers\Models\NodePermission;
use Gcl\GclUsers\Models\PermissionRole;
class NodePermissionControllerTest extends TestCase
{
    public function testCreateNodePermissionFailure()
    {
        $this->withoutMiddleware();
        $res = $this->call('POST', '/nodePermission');
        $this->assertEquals(400, $res->getStatusCode());

        $results = json_decode($res->getContent());
        $this->assertEquals('error', $results->status);
        $this->assertEquals('validation', $results->type);
        $this->assertObjectHasAttribute('name', $results->errors);
        $this->assertEquals('The name field is required.', $results->errors->name[0]);

        $res = $this->call('POST', '/nodePermission', [
            'name' => 'Login',
            'display_name' => 'User login',
            'description' => 'Des',
            'parent_id' => -1
        ]);
        $this->assertEquals(400, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('Parent id is invalid.', $results);
    }

    public function testCreateNodePermissionSuccess()
    {
        $this->withoutMiddleware();
        $res = $this->call('POST', '/nodePermission', [
            'name' => 'Login',
            'display_name' => 'User login',
            'description' => 'Des',
            'parent_id' => 1
        ]);

        $this->assertEquals(201, $res->getStatusCode());

        $results = json_decode($res->getContent());

        $this->assertEquals('[{"id":2,"name":"Login","display_name":"User login"}]', $results->entities);
    }

    public function testCreateNodePermissionWithoutParentSuccess()
    {
        $this->withoutMiddleware();
        $res = $this->call('POST', '/nodePermission', [
            'name' => 'Login',
            'display_name' => 'User login',
            'description' => 'Des'
        ]);

        $this->assertEquals(201, $res->getStatusCode());

        $results = json_decode($res->getContent());

        $this->assertEquals('[{"id":2,"name":"Login","display_name":"User login"}]', $results->entities);
    }

    public function testGetListPermissionNull()
    {
        $this->withoutMiddleware();
        $this->call('POST', '/nodePermission', [
            'name' => 'Login',
            'display_name' => 'User login',
            'description' => 'Des',
            'parent_id' => 1
        ]);

        // CHange root node id
        $root = NodePermission::find(1);
        $root->id = 0;
        $root->save();

        $res = $this->call('GET', '/nodePermission');

        $this->assertEquals(201, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(null, $results->entities);
    }

    public function testGetListPermissionSuccess()
    {
        $this->withoutMiddleware();
        $this->call('POST', '/nodePermission', [
            'name' => 'Login',
            'display_name' => 'User login',
            'description' => 'Des',
            'parent_id' => 1
        ]);

        $res = $this->call('GET', '/nodePermission');

        $this->assertEquals(201, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('[{"id":2,"name":"Login","display_name":"User login"}]', $results->entities);
    }

    public function testUpdateInfoFailure()
    {
        $this->withoutMiddleware();
        // Test permission node not found
        $res = $this->call('PATCH', '/nodePermission/-1', [
            'name' => 'Root2',
        ]);

        $this->assertEquals(404, $res->getStatusCode());

        // Test validate failure
        $this->call('POST', '/nodePermission', [
            'name' => 'Login',
            'display_name' => 'User login',
            'description' => 'Des',
            'parent_id' => 1
        ]);

        $res = $this->call('PATCH', '/nodePermission/2');

        $this->assertEquals(400, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('The name field is required.', $results->errors->name[0]);
    }

    public function testUpdateInfoSuccess()
    {
        $this->withoutMiddleware();
        $this->call('POST', '/nodePermission', [
            'name' => 'Login',
            'display_name' => 'User login',
            'description' => 'Des',
            'parent_id' => 1
        ]);

        $res = $this->call('PATCH', '/nodePermission/2', [
            'name' => 'Login edited',
            'display_name' => 'User login edited',
            'description' => 'Des edited',
        ]);

        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(2, $results->entities->id);
        $this->assertEquals('Login edited', $results->entities->name);
        $this->assertEquals('User login edited', $results->entities->display_name);
        $this->assertEquals('Des edited', $results->entities->description);
    }

    public function testDeleteFailure()
    {
        $this->withoutMiddleware();
        $res = $this->call('DELETE', '/nodePermission/-1');

        $this->assertEquals(404, $res->getStatusCode());
    }

    public function testDeleteSuccess()
    {
        $this->withoutMiddleware();
        $this->call('POST', '/nodePermission', [
            'name' => 'Login',
            'display_name' => 'User login',
            'description' => 'Des',
            'parent_id' => 1
        ]);

        $res = $this->call('DELETE', '/nodePermission/2');

        $this->assertEquals(204, $res->getStatusCode());
    }

    public function testUpdateTree()
    {
        $this->withoutMiddleware();
        $res = $this->call('POST', '/nodePermission/tree', [
            'data' => '[{"id":2, "name":"2"},{"id":7, "name":"7","children":[{"id":4, "name":"4","children":[{"id":5, "name":"5"},{"id":6, "name":"6"}]}]},{"id":3, "name":"3"}]',
        ]);
        $res = $this->call('POST', '/nodePermission/tree', [
            'data' => '[{"id":2, "name":"2"},{"id":3, "name":"3","children":[{"id":4, "name":"4","children":[{"id":5, "name":"5"},{"id":6, "name":"6"}]}]},{"id":7, "name":"7"}]',
        ]);
        $this->assertEquals(201, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('[{"id":2,"name":"2","display_name":null},{"id":7,"name":"7","display_name":null},{"id":3,"name":"3","display_name":null,"children":[{"id":4,"name":"4","display_name":null,"children":[{"id":5,"name":"5","display_name":null},{"id":6,"name":"6","display_name":null}]}]}]', $results->entities);
    }

    public function testSaveAndListRolePermission()
    {
        $this->withoutMiddleware();
        // Test post roles permission failure
        $res = $this->call('POST', '/roles/1/permission');
        $this->assertEquals(400, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('The permission id field is required.', $results->errors->permission_id[0]);
        $this->assertEquals('The status field is required.', $results->errors->status[0]);

        // Test roles not found
        $res = $this->call('GET', '/roles/100/permission');
        $this->assertEquals(404, $res->getStatusCode());

        // Test tree permission empty
        $res = $this->call('GET', '/roles/1/permission');
        $this->assertEquals(201, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('[]', $results->entities);

        // Post null tree
        $res = $this->call('POST', '/nodePermission/tree', [
            'data' => '[]',
        ]);
        $this->assertEquals(201, $res->getStatusCode());

        // Post permission tree
        $this->call('POST', '/nodePermission/tree', [
            'data' => '[{"id":2, "name":"2"},{"id":3, "name":"3","children":[{"id":4, "name":"4","children":[{"id":5, "name":"5"},{"id":6, "name":"6"}]}]},{"id":7, "name":"7"}]',
        ]);

        // Post permission to role
        $res = $this->call('POST', '/roles/1/permission', [
            'permission_id' => 2,
            'status'        => 1
        ]);
        $this->assertEquals(201, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('{"id":2,"permission_id":2,"role_id":1,"status":1}', $results->entities);

        $res = $this->call('POST', '/roles/1/permission', [
            'permission_id' => 2,
            'status'        => 0
        ]);
        $this->assertEquals(201, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('{"id":2,"permission_id":2,"role_id":1,"status":0}', $results->entities);

        $res = $this->call('POST', '/roles/1/permission', [
            'permission_id' => 3,
            'status'        => 1
        ]);

        // Test list role permissions
        $res = $this->call('GET', '/roles/1/permission');
        $this->assertEquals(201, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('[{"id":2,"name":"2","display_name":null,"status":0},{"id":3,"name":"3","display_name":null,"status":1,"children":[{"id":4,"name":"4","display_name":null,"status":0,"children":[{"id":5,"name":"5","display_name":null,"status":0},{"id":6,"name":"6","display_name":null,"status":0}]}]},{"id":7,"name":"7","display_name":null,"status":0}]', $results->entities);
    }

    public function testRouteToPermission()
    {
        $this->withoutMiddleware();
        // Test validate failure
        $res = $this->call('POST', '/nodePermission/1/route');
        $this->assertEquals(400, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('error', $results->status);
        $this->assertEquals('validation', $results->type);
        $this->assertObjectHasAttribute('route_name', $results->errors);
        $this->assertEquals('The route name field is required.', $results->errors->route_name[0]);
        $this->assertObjectHasAttribute('route_method', $results->errors);
        $this->assertEquals('The route method field is required.', $results->errors->route_method[0]);

        // Test permission not found
        $res = $this->call('POST', '/nodePermission/-1/route', [
            'route_name' => 'me/password',
            'route_method' => 'PUT'
        ]);
        $this->assertEquals(404, $res->getStatusCode());

        // Post permission tree
        $this->call('POST', '/nodePermission/tree', [
            'data' => '[{"id":2, "name":"2"},{"id":3, "name":"3","children":[{"id":4, "name":"4","children":[{"id":5, "name":"5"},{"id":6, "name":"6"}]}]},{"id":7, "name":"7"}]',
        ]);

        // Test add route success
        $res = $this->call('POST', '/nodePermission/2/route', [
            'route_name' => 'me/password',
            'route_method' => 'PUT'
        ]);
        $this->assertEquals(201, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('me/password', $results->entities[0]->route_name);
        $this->assertEquals('2', $results->entities[0]->permission_id);
        $this->assertEquals('PUT', $results->entities[0]->route_method);

        $this->call('POST', '/nodePermission/2/route', [
            'route_name' => 'me',
            'route_method' => 'GET'
        ]);

        // Test list routes of permission failure
        $res = $this->call('GET', '/nodePermission/-1/route');
        $this->assertEquals(404, $res->getStatusCode());

        // Test list routes of permission success
        $res = $this->call('GET', '/nodePermission/2/route');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('1', $results->entities[0]->id);
        $this->assertEquals('me/password', $results->entities[0]->route_name);
        $this->assertEquals('PUT', $results->entities[0]->route_method);

        $this->assertEquals('me', $results->entities[1]->route_name);
        $this->assertEquals('GET', $results->entities[1]->route_method);

        // Test delete route of permission failure
        $res = $this->call('DELETE', '/permissionRoute/-1');
        $this->assertEquals(404, $res->getStatusCode());

        // Test delete route of permission success
        $res = $this->call('DELETE', '/permissionRoute/2');
        $this->assertEquals(204, $res->getStatusCode());

        $res = $this->call('GET', '/nodePermission/2/route');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('1', $results->entities[0]->id);
        $this->assertEquals('me/password', $results->entities[0]->route_name);
        $this->assertEquals('PUT', $results->entities[0]->route_method);
    }
}
