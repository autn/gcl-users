<?php

use App\User as AppUser;
use Gcl\GclUsers\Models\User;
use Gcl\GclUsers\Models\Role;

class TokenTest extends TestCase
{
    public function testToken()
    {
        $credentials = [ 'email' => 'admin@example.com', 'password' => '123456' ];
        $token = JWTAuth::attempt($credentials);

        $res = $this->call('GET', '/me', [], [], [], ['HTTP_Authorization' => "Bearer {$token}"]);
    }
}
