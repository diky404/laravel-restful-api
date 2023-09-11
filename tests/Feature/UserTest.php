<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testRegisterSuccess()
    {
        $this->post('/api/users', [
            "username" => "omi",
            "password" => "rahasia",
            "name" => "omi"
        ])->assertStatus(201)
          ->assertJson([
            "data" => [
                "username" => "omi",
                "name" => "omi"
            ]
          ]);
    }
    public function testRegisterFailed()
    {
        $this->post('/api/users', [
            "username" => "",
            "password" => "",
            "name" => ""
        ])->assertStatus(400)
          ->assertJson([
            "errors" => [
                "username" => ["The username field is required."],
                "password" => ["The password field is required."],
                "name" => ["The name field is required."]
            ]
          ]);
    }
    public function testRegisterUsernameAlreadyExists()
    {
        $this->testRegisterSuccess();

        $this->post('/api/users', [
            "username" => "omi",
            "password" => "rahasia",
            "name" => "omi"
        ])->assertStatus(400)
          ->assertJson([
            "errors" => [
                "username" => ["Username has already registered"]
            ]
          ]);
    }

    public function testLoginSuccess()
    {
        $this->seed([UserSeeder::class]);

        $this->post('/api/users/login', [
            'username' => 'test',
            'password' => 'test'
        ])->assertStatus(200)
          ->assertJson([
            'data' => [
                'username' => 'test',
                'name' => 'test'
            ]
          ]);

        $user = User::query()->where('username', 'test')->first();
        self::assertNotNull($user->token);
    }

    public function testLoginFailed()
    {
        $this->seed([UserSeeder::class]);

        $this->post('/api/users/login', [
            'username' => 'tests',
            'password' => 'test'
        ])->assertStatus(401)
          ->assertJson([
            'errors' => [
                'message' => [
                    'Username or password wrong'
                ]
            ]
          ]);
    }

    public function testGetSuccess()
    {
        $this->seed([UserSeeder::class]);

        $this->get('/api/users/current', [
            'Authorization' => 'test'
        ])->assertStatus(200)
          ->assertJson([
            'data' => [
                'username' => 'test',
                'name' => 'test'
            ]
          ]);
    }

    public function testGetUnauthorized()
    {
        $this->seed([UserSeeder::class]);

        $this->get('/api/users/current')
             ->assertStatus(401)
             ->assertJson([
                'errors' => [
                    'message' => [
                        'unauthorized'
                    ]
                ]
             ]);
    }

    public function testGetInvalidToken()
    {
        $this->seed([UserSeeder::class]);

        $this->get('/api/users/current', [ 'Authorization' => 'salah' ])
             ->assertStatus(401)
             ->assertJson([
                'errors' => [
                    'message' => [
                        'unauthorized'
                    ]
                ]
             ]);
    }

    public function testUpdateNameSuccess()
    {
        $this->seed([UserSeeder::class]);

        $this->patch('/api/users/current', [ 'name' => 'baru' ], [
            'Authorization' => 'test'
        ])->assertStatus(200)
          ->assertJson([
            'data' => [
                'username' => 'test',
                'name' => 'baru'
            ]
          ]);
    }

    public function testUpdatePasswordSuccess()
    {
        $this->seed([UserSeeder::class]);

        $this->patch('/api/users/current', [ 'password' => 'baru' ], [
            'Authorization' => 'test'
        ])->assertStatus(200)
          ->assertJson([
            'data' => [
                'username' => 'test',
                'name' => 'test'
            ]
          ]);
    }

    public function testUpdateFailed()
    {
        $this->seed([UserSeeder::class]);

        $this->patch('/api/users/current', [ 'name' => 'barubarubarubarubarubaru' ], [
            'Authorization' => 'test'
        ])->assertStatus(400)
          ->assertJson([
            'errors' => [
                'name' => [
                    'The name field must not be greater than 20 characters.'
                ]
            ]
          ]);
    }

    public function testLogoutSuccess()
    {
        $this->seed([UserSeeder::class]);

        $this->delete(uri:'/api/users/logout', headers: [
            'Authorization' => 'test'
        ])->assertStatus(200)->assertJson([
            "data" => true
        ]);
    }

    public function testLogoutFailed()
    {
        $this->seed([UserSeeder::class]);

        $this->delete('/api/users/logout', [
            'Authorization' => 'tests'
        ])->assertStatus(401)->assertJson([
            'errors' => [
                'message' => [
                    'unauthorized'
                ]
            ]
        ]);
    }
}
