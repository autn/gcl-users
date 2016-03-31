# Laravel Users Module

[![Build Status](https://travis-ci.org/php-soft/laravel-users.svg)](https://travis-ci.org/php-soft/laravel-users)

> This module is use JWTAuth and ENTRUST libraries
>
> 1. https://github.com/tymondesigns/jwt-auth (JSON Web Token)
> 2. https://github.com/Zizaco/entrust (Role-based Permissions)
> 3. https://github.com/php-soft/laravel-users (Users manager)

## 1. Installation

Install via composer - edit your `composer.json` to require the package.

```js
"require": {
    // ...
    "zizaco/entrust": "dev-laravel-5",
    "autn/gcl-users": "2.x"
}
```
## Version Compatibility

 GclUsers   | Laravel
:-----------|:----------
 1.x        | 5.1.x
 2.x        | 5.2.x


Then run `composer update` in your terminal to pull it in.
Once this has finished, you will need to add the service provider to the `providers` array in your `app.php` config as follows:

```php
'providers' => [
    // ...
    PhpSoft\ArrayView\Providers\ArrayViewServiceProvider::class,
    Gcl\GclUsers\Providers\UserServiceProvider::class,
    Tymon\JWTAuth\Providers\JWTAuthServiceProvider::class,
    Zizaco\Entrust\EntrustServiceProvider::class,
    Baum\Providers\BaumServiceProvider::class,
]
```

Next, also in the `app.php` config file, under the `aliases` array, you may want to add facades.

```php
'aliases' => [
    // ...
    'JWTAuth'   => Tymon\JWTAuth\Facades\JWTAuth::class,
    'JWTFactory'=> Tymon\JWTAuth\Facades\JWTFactory::class,
    'Entrust'   => Zizaco\Entrust\EntrustFacade::class,
```

You will want to publish the config using the following command:

```sh
$ php artisan vendor:publish --provider="Gcl\GclUsers\Providers\UserServiceProvider"
```

***Don't forget to set a secret key in the jwt config file!***

I have included a helper command to generate a key as follows:

```sh
$ php artisan jwt:generate
```

this will generate a new random key, which will be used to sign your tokens.

## 2. Migration and Seeding

Now generate the migration:

```sh
$ php artisan gcl-users:migrate
```

It will generate the `<timestamp>_entrust_setup_tables.php` migration. You may now run it with the artisan migrate command:

```sh
$ php artisan migrate
```

Running Seeders with command:

```sh
$ php artisan db:seed --class=UserModuleSeeder
```

Note: Run seeders after use `UserTrait` in your existing `App\User` model, follow 3.2 below

## 3. Usage

### 3.1. Authenticate with JSON Web Token

You need to change class `App\User` to inherit from `Gcl\GclUsers\Models\User` as follows:

```php
namespace App;

// ...
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Gcl\GclUsers\Models\User as GclUser;

class User extends GclUser implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword;

    // ...

    // You need allows fill attributes as follows
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'location',
        'country',
        'biography',
        'occupation',
        'website',
        'image',
        'birthday',
        'gender'
    ];

    // ...
}
```

Remove middlewares in `app/Http/Kernel.php`

* `\App\Http\Middleware\EncryptCookies::class`
* `\App\Http\Middleware\VerifyCsrfToken::class`

Add route middlewares in `app/Http/Kernel.php`

```php
protected $routeMiddleware = [
    // ...
    'jwt.auth' => \Gcl\GclUsers\Middleware\Authenticate::class,
    'jwt.refresh' => \Tymon\JWTAuth\Middleware\RefreshToken::class,
];
```

Add routes in `app/Http/routes.php`

```php
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
```

Note: You can add this to your middleware groups `api`

Apache seems to discard the Authorization header if it is not a base64 encoded user/pass combo. So to fix this you can add the following to your apache config

```
RewriteEngine On

RewriteCond %{HTTP:Authorization} ^(.*)
RewriteRule .* - [e=HTTP_AUTHORIZATION:%1]
```

Alternatively you can include the token via a query string

```
http://api.mysite.com/me?token={yourtokenhere}
```

### 3.2. Role-based Permissions

Use the `UserTrait` trait in your existing `App\User` model. For example:

```php
namespace App;

// ...
use Gcl\GclUsers\Models\UserTrait;

class User extends GclUser implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use UserTrait, Authenticatable, CanResetPassword; // add this trait to your user model
    // ...
}
```

Create `Role` and `Permission` follows

```php
// create role admin (default this role has been created on UserModuleSeeder)
$admin = new Role();
$admin->name         = 'admin';
$admin->display_name = 'User Administrator'; // optional
$admin->description  = 'User is allowed to manage and edit other users'; // optional
$admin->save();

// role attach alias
$user->attachRole($admin); // parameter can be an Role object, array, or id

// or eloquent's original technique
$user->roles()->attach($admin->id); // id only

// create permission
$createPost = new NodePermission();
$createPost->name         = 'create-post';
$createPost->display_name = 'Create Posts'; // optional
$createPost->description  = 'create new blog posts'; // optional
$createPost->parent_id    = 1 // optional
$createPost->save();

$admin->attachPermission($createPost);
// equivalent to $admin->perms()->sync(array($createPost->id));
```

Now we can check for roles and permissions simply by doing:

```php
$user->hasRole('owner');   // false
$user->hasRole('admin');   // true
$user->can('edit-user');   // false
$user->can('create-post'); // true
```

Both `hasRole()` and `can()` can receive an array of roles & permissions to check:

```php
$user->hasRole(['owner', 'admin']);       // true
$user->can(['edit-user', 'create-post']); // true
```

### 3.3 Forgot password

To send mail forgot password,
- You need to add address and name of sender in `config\mail.php` as follows:

```php
'from' => ['address' => 'no-reply@example.com', 'name' => 'System'],
```

- You need to create email view:
Create  `password.blade.php` file in folder `resources\views\emails` with contents as follows:

```php
<h3>You are receiving this e-mail because you requested resetting your password to domain.com</h3>
Please click this URL to reset your password: <a href="http://domain.com/passwords/reset?token={{$token}}">http://domain.com/passwords/reset?token={{$token}}</a>
```
You can change contents of this view for your using.

By other way, you can use other view and config `password.email` in `config\auth.php`:

```php
    'password' => [
        'email' => 'emails.password',
        'table' => 'password_resets',
        'expire' => 60,
    ],
```

### 3.4 Middlewares

#### Gcl\GclUsers\Middleware\RoutePermission

This middleware is used to check permission for a route dynamic by database.

Add route middlewares in app/Http/Kernel.php

```php
protected $routeMiddleware = [
    // ...
    'routePermission' => \Gcl\GclUsers\Middleware\RoutePermission::class,
];
```

Usage

```php
Route::group(['middleware'=>'routePermission'], function() {
    Route::post('/blog', function () {
        //
    });
});
```

Require permission for a route as follows

```php

// require permissions or roles
Gcl\GclUsers\Models\RoutePermission::setRoutePermissionsRoles(2, '/blog', 'POST');
```

#### Gcl\GclUsers\Middleware\Validate

This middleware is used to check validate for fields on different applications which use this package.

Add route middlewares in app/Http/Kernel.php

```php
protected $routeMiddleware = [
    // ...
    'validate'   => \Gcl\GclUsers\Middleware\Validate::class,
];
```

Usage

```php
Route::post('/user', ['middleware'=>'validate: App\Http\Validators\UserValidate',
    function () {
        //
    }
]);
```
With `App\Http\Validators\UserValidate`, it's class which you need to declare in route. This class is used to declare rules to validate.

You can also use other class to declare rules for validate in your application but It have to implements `Gcl\GclUsers\Contracts\Validator` class.

For example, I declared rules in `App\Http\Validators\UserValidate` class as follows:

```php
use Gcl\GclUsers\Contracts\Validator;

/**
 * User Validate
 *
 * return array
 */
class UserValidate implements Validator
{
    /**
     * Custom validator
     *
     * @return boolean
     */
    public static function boot($request)
    {

        IlluminateValidator::extend('validate_name', function($attribute, $value, $parameters) {

                return $value == 'validate_name';
            }, 'The name is in valid.'
        );
    }

    /**
     * Declare rules
     *
     * @return array
     */
    public static function rules()
    {
        return [
            'name'     => 'required|max:255|validate_name',
            'email'    => 'required|email',
            'password' => 'required|confirmed|min:6'
        ];
    }
}
```
Here, you will declare fields that you want to validate them in `rules()` function. And You can also custom validator fields that you want by declare them in `boot()` function.
