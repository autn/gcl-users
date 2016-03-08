<?php

use Route as R;
use Gcl\GclUsers\Models\Role;
use Gcl\GclUsers\Models\Permission;
use Gcl\GclUsers\Models\PermissionRoute;
use Gcl\GclUsers\Models\PermissionRole;
use Gcl\GclUsers\Models\NodePermission;
use Tymon\JWTAuth\Facades\JWTAuth;

class PermissionRouteTest extends TestCase
{
    public function testNotLogin()
    {
        $res = $this->call('POST', '/blog/1');
        $this->assertEquals(401, $res->getStatusCode());
    }

    public function testGetAllRoutes()
    {
        $this->withoutMiddleware();
        $res = $this->call('GET', '/routes');
        $this->assertEquals(200, $res->getStatusCode());
        $result = json_decode($res->getContent());
        $routes = R::getRoutes();
        foreach ($routes as $key => $route) {
            $method = is_array($method = $route->getMethods()) ? $method[0] : $method;
            $name = $route->getPath() === '/' ? $route->getPath() : '/' . $route->getPath();

            $this->assertEquals($method, $result->entities[$key]->route_method);
            $this->assertEquals($name, $result->entities[$key]->route_name);
        }
    }

    public function testGetAllRoutesNotTreeAndAdminPermission()
    {
        $credentials = [ 'email' => 'admin@example.com', 'password' => '123456' ];
        $token = JWTAuth::attempt($credentials);

        // Post permission tree
        NodePermission::model()->tree('[{"id":2, "name":"2"},{"id":3, "name":"3","children":[{"id":4, "name":"4","children":[{"id":5, "name":"5"},{"id":6, "name":"6"}]}]},{"id":7, "name":"7"}]');

        // Test no route added to permission tree
        $res = $this->call('GET', '/routesNotTree', [], [], [], ['HTTP_Authorization' => "Bearer {$token}"]);
        $this->assertEquals(200, $res->getStatusCode());
        $result = json_decode($res->getContent());

        $countAll = count($result->entities);

        $res2 = $this->call('GET', '/routes', [], [], [], ['HTTP_Authorization' => "Bearer {$token}"]);
        $result2 = json_decode($res2->getContent());
        // dd($result2);
        $allRoutes = $result2->entities;

        foreach ($allRoutes as $key => $value) {
            $this->assertEquals($value->route_method, $result->entities[$key]->route_method);
            $this->assertEquals($value->route_name, $result->entities[$key]->route_name);
        }

        // add route to permission
        PermissionRoute::setRoutePermissionsRoles(2, '/', 'GET');
        PermissionRoute::setRoutePermissionsRoles(2, '/me', 'GET');

        // Test 2 routes added to permission tree
        $res = $this->call('GET', '/routesNotTree', [], [], [], ['HTTP_Authorization' => "Bearer {$token}"]);
        $this->assertEquals(200, $res->getStatusCode());
        $result = json_decode($res->getContent());

        $count = count($result->entities);

        $this->assertNotEquals('/', $result->entities[0]->route_name);
        $this->assertEquals(2, $countAll - $count);
    }

    public function testNotHasAllPermission()
    {
        $user = factory(App\User::class)->create(['password'=>bcrypt('123456')]);
        $credentials = [ 'email' => $user->email, 'password' => '123456' ];
        $token = JWTAuth::attempt($credentials);

        $res = $this->call('POST', '/blog/1', [], [], [], ['HTTP_Authorization' => "Bearer {$token}"]);
        $this->assertEquals(403, $res->getStatusCode());

        // assign new role with name
        $editor = factory(Role::class)->create(['name' => 'editor', 'active' => 1]);

        // add role to user
        $user->attachRole($editor);

        $res = $this->call('POST', '/blog/1', [], [], [], ['HTTP_Authorization' => "Bearer {$token}"]);
        $this->assertEquals(403, $res->getStatusCode());

        // set all permisson (false)
        PermissionRole::create([
            'permission_id' => 1,
            'role_id' => $editor->id,
            'status' => 0
        ]);

        $res = $this->call('POST', '/blog/1', [], [], [], ['HTTP_Authorization' => "Bearer {$token}"]);
        $this->assertEquals(403, $res->getStatusCode());
    }

    public function testHasAllPermission()
    {
        // assign new role with name
        $editor = factory(Role::class)->create(['name' => 'editor', 'active' => 1]);

        $user = factory(App\User::class)->create(['password'=>bcrypt('123456')]);
        $credentials = [ 'email' => $user->email, 'password' => '123456' ];
        $token = JWTAuth::attempt($credentials);

        // add role to user
        $user->attachRole($editor);

        // set all permisson
        PermissionRole::create([
            'permission_id' => 1,
            'role_id' => $editor->id,
            'status' => 1
        ]);

        $res = $this->call('POST', '/blog/1', [], [], [], ['HTTP_Authorization' => "Bearer {$token}"]);
        $this->assertEquals(200, $res->getStatusCode());
    }

    public function testNotHasAPermission()
    {
        // assign new role with name
        $editor = factory(Role::class)->create(['name' => 'editor', 'active' => 1]);

        $user = factory(App\User::class)->create(['password'=>bcrypt('123456')]);
        $credentials = [ 'email' => $user->email, 'password' => '123456' ];
        $token = JWTAuth::attempt($credentials);

        // add role to user
        $user->attachRole($editor);

        // Post permission tree
        NodePermission::model()->tree('[{"id":2, "name":"2"},{"id":3, "name":"3","children":[{"id":4, "name":"4","children":[{"id":5, "name":"5"},{"id":6, "name":"6"}]}]},{"id":7, "name":"7"}]');

        $res = $this->call('POST', '/blog/1', [], [], [], ['HTTP_Authorization' => "Bearer {$token}"]);
        $this->assertEquals(403, $res->getStatusCode());

        // add route to permission
        PermissionRoute::setRoutePermissionsRoles(2, '/blog/{id}', 'POST');

        $res = $this->call('POST', '/blog/1', [], [], [], ['HTTP_Authorization' => "Bearer {$token}"]);
        $this->assertEquals(403, $res->getStatusCode());

        // set a permisson
        PermissionRole::create([
            'permission_id' => 2,
            'role_id' => $editor->id,
            'status' => 0
        ]);

        $res = $this->call('POST', '/blog/1', [], [], [], ['HTTP_Authorization' => "Bearer {$token}"]);
        $this->assertEquals(403, $res->getStatusCode());
    }

    public function testHasAPermission()
    {
        // assign new role with name
        $editor = factory(Role::class)->create(['name' => 'editor', 'active' => 1]);

        $user = factory(App\User::class)->create(['password'=>bcrypt('123456')]);
        $credentials = [ 'email' => $user->email, 'password' => '123456' ];
        $token = JWTAuth::attempt($credentials);

        // add role to user
        $user->attachRole($editor);

        // Post permission tree
        NodePermission::model()->tree('[{"id":2, "name":"2"},{"id":3, "name":"3","children":[{"id":4, "name":"4","children":[{"id":5, "name":"5"},{"id":6, "name":"6"}]}]},{"id":7, "name":"7"}]');

        // add routes to permission
        PermissionRoute::setRoutePermissionsRoles(2, '/password', 'PATCH');
        PermissionRoute::setRoutePermissionsRoles(2, '/blog/{id}', 'POST');

        // set a permisson
        PermissionRole::create([
            'permission_id' => 2,
            'role_id' => $editor->id,
            'status' => 1
        ]);

        $res = $this->call('POST', '/blog/1', [], [], [], ['HTTP_Authorization' => "Bearer {$token}"]);
        $this->assertEquals(200, $res->getStatusCode());
    }

    public function testHasManyPermission()
    {
        // assign new roles with name
        $modify = factory(Role::class)->create(['name' => 'modify', 'active' => 1]);
        $editor = factory(Role::class)->create(['name' => 'editor', 'active' => 1]);

        $user = factory(App\User::class)->create(['password'=>bcrypt('123456')]);
        $credentials = [ 'email' => $user->email, 'password' => '123456' ];
        $token = JWTAuth::attempt($credentials);

        // add roles to user
        $user->attachRole($modify);
        $user->attachRole($editor);

        // Post permission tree
        NodePermission::model()->tree('[{"id":2, "name":"2"},{"id":3, "name":"3","children":[{"id":4, "name":"4","children":[{"id":5, "name":"5"},{"id":6, "name":"6"}]}]},{"id":7, "name":"7"}]');

        // add route to permission
        PermissionRoute::setRoutePermissionsRoles(2, '/password', 'PATCH');
        PermissionRoute::setRoutePermissionsRoles(2, '/blog/{id}', 'POST');

        // set permissons
        PermissionRole::create([
            'permission_id' => 2,
            'role_id' => $modify->id,
            'status' => 0
        ]);
        PermissionRole::create([
            'permission_id' => 2,
            'role_id' => $editor->id,
            'status' => 0
        ]);

        $res = $this->call('POST', '/blog/1', [], [], [], ['HTTP_Authorization' => "Bearer {$token}"]);
        $this->assertEquals(403, $res->getStatusCode());

        // set permissons
        PermissionRole::create([
            'permission_id' => 2,
            'role_id' => $modify->id,
            'status' => 1
        ]);
        PermissionRole::create([
            'permission_id' => 2,
            'role_id' => $editor->id,
            'status' => 0
        ]);

        $res = $this->call('POST', '/blog/1', [], [], [], ['HTTP_Authorization' => "Bearer {$token}"]);
        $this->assertEquals(200, $res->getStatusCode());

        // set permissons
        PermissionRole::create([
            'permission_id' => 2,
            'role_id' => $modify->id,
            'status' => 0
        ]);
        PermissionRole::create([
            'permission_id' => 2,
            'role_id' => $editor->id,
            'status' => 1
        ]);

        $res = $this->call('POST', '/blog/1', [], [], [], ['HTTP_Authorization' => "Bearer {$token}"]);
        $this->assertEquals(200, $res->getStatusCode());
    }
}
