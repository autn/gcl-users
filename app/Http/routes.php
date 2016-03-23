<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('/auth/login', '\Gcl\GclUsers\Controllers\AuthController@login');

Route::group(['middleware'=>'jwt.auth'], function() {
    Route::post('/auth/logout', '\Gcl\GclUsers\Controllers\AuthController@logout');
    Route::get('/me', '\Gcl\GclUsers\Controllers\UserController@authenticated');
    Route::patch('/me', '\Gcl\GclUsers\Controllers\UserController@update');
    Route::put('/me/password', '\Gcl\GclUsers\Controllers\PasswordController@change');
});

Route::post('/passwords/forgot', '\Gcl\GclUsers\Controllers\PasswordController@forgot');
Route::post('/passwords/reset', '\Gcl\GclUsers\Controllers\PasswordController@reset');
Route::group(['middleware'=>'routePermission'], function() {
    Route::get('/users/trash', '\Gcl\GclUsers\Controllers\UserController@index');
    Route::post('/users', '\Gcl\GclUsers\Controllers\UserController@store');
    Route::get('/users/{id}', '\Gcl\GclUsers\Controllers\UserController@show');
    Route::get('/users', '\Gcl\GclUsers\Controllers\UserController@index');
    Route::delete('/users/{id}', '\Gcl\GclUsers\Controllers\UserController@destroy');
    Route::post('/users/{id}/trash', '\Gcl\GclUsers\Controllers\UserController@moveToTrash');
    Route::post('/users/{id}/restore', '\Gcl\GclUsers\Controllers\UserController@restoreFromTrash');
    Route::patch('/users/{id}', '\Gcl\GclUsers\Controllers\UserController@update');
    Route::post('/users/{id}/block', '\Gcl\GclUsers\Controllers\UserController@block');
    Route::post('/users/{id}/unblock', '\Gcl\GclUsers\Controllers\UserController@unblock');
    Route::post('/users/{id}/roles', '\Gcl\GclUsers\Controllers\UserController@assignRole');
    Route::get('/users/{id}/roles', '\Gcl\GclUsers\Controllers\RoleController@indexByUser');

    Route::get('/roles', '\Gcl\GclUsers\Controllers\RoleController@index');
    Route::get('/roles/{id}', '\Gcl\GclUsers\Controllers\RoleController@show');
    Route::post('/roles', '\Gcl\GclUsers\Controllers\RoleController@store');
    Route::patch('/roles/{id}', '\Gcl\GclUsers\Controllers\RoleController@update');
    Route::delete('/roles/{id}', '\Gcl\GclUsers\Controllers\RoleController@destroy');

    Route::get('/nodePermission', '\Gcl\GclUsers\Controllers\NodePermissionController@index');
    Route::post('/nodePermission', '\Gcl\GclUsers\Controllers\NodePermissionController@store');
    Route::patch('/nodePermission/{id}', '\Gcl\GclUsers\Controllers\NodePermissionController@updateInfo');
    Route::delete('/nodePermission/{id}', '\Gcl\GclUsers\Controllers\NodePermissionController@destroy');
    Route::post('/nodePermission/tree', '\Gcl\GclUsers\Controllers\NodePermissionController@updateTree');
    Route::get('/roles/{id}/permission', '\Gcl\GclUsers\Controllers\NodePermissionController@getRolePerm');
    Route::get('/roles/{id}/allPermission', '\Gcl\GclUsers\Controllers\NodePermissionController@checkAllPerm');
    Route::post('/roles/{id}/permission', '\Gcl\GclUsers\Controllers\NodePermissionController@storePermToRole');
    Route::get('/nodePermission/{id}/route', '\Gcl\GclUsers\Controllers\PermissionRouteController@index');
    Route::post('/nodePermission/{id}/route', '\Gcl\GclUsers\Controllers\PermissionRouteController@store');
    Route::delete('/permissionRoute/{id}', '\Gcl\GclUsers\Controllers\PermissionRouteController@destroy');

    Route::get('/routes', '\Gcl\GclUsers\Controllers\PermissionRouteController@getAllRoutes');
    Route::get('/routesNotTree', '\Gcl\GclUsers\Controllers\PermissionRouteController@getAllRoutesNotTree');
});
