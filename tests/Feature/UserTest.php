<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testRegisterSuccess(): void
    {
        $this->post('/api/users', [
            'username' => 'peter',
            'password' => 'rahasia',
            'name' => 'peter-28'
        ])
            ->assertStatus(201)
            ->assertJson([
                'data' => [
                    "username" => 'peter',
                    "name" => 'peter-28'
                ],
            ]);
    }

    public function testRegisterFailed(): void
    {
        $this->post('/api/users', [
            'username' => '',
            'password' => '',
            'name' => ''
        ])->assertStatus(400)
            ->assertJson([
                'errors' => [
                    "username" => ["The username field is required."],
                    "password" => ["The password field is required."],
                    "name" => ["The name field is required."]
                ]
            ]);
    }

    public function testRegisterUsernameAlreadyExists(): void
    {
        $this->testRegisterSuccess();
        $this->post('/api/users', [
            'username' => 'peter',
            'password' => 'rahasia',
            'name' => 'peter'
        ])->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'username' => 'username already exists',
                ]
            ]);
    }

    public function testLoginSuccess()
    {
        $this->seed([UserSeeder::class]);
        $this->post('/api/users/login', [
            'username' => 'test',
            'password' => 'test',
        ])
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => 'test',
                    'name' => 'test',
                ],
            ]);

        $user = User::where('username', 'test')->first();
        self::assertNotNull($user->token);
    }

    public function testLoginFailedUsernameNotFound()
    {
        $this->post('/api/users/login', [
            'username' => 'test',
            'password' => 'test',
        ])
            ->assertStatus(401)
            ->assertJson([
                'messages' => [
                    'username' => 'username or password is wrong',
                ],
            ]);
    }

    public function testLoginFailedPasswordWrong()
    {
        $this->seed([UserSeeder::class]);
        $this->post('/api/users/login', [
            'username' => 'test',
            'password' => 'salah',
        ])
            ->assertStatus(401)
            ->assertJson([
                'messages' => [
                    'username' => 'username or password is wrong',
                ],
            ]);
    }

    public function testGetSuccess()
    {
        $this->seed([UserSeeder::class]);
        $this->get('/api/users/current', ['Authorization' => 'test'])
            ->assertStatus(200)
            ->assertJson([
                "data" => [
                    "username" => 'test',
                    "name" => 'test'
                ]
            ]);
    }

    public function testGetUnauthorized()
    {
        $this->seed([UserSeeder::class]);
        $this->get('/api/users/current')
            ->assertStatus(401)
            ->assertJson([
                "errors" => [
                    "message" => [
                        "unauthorized"
                    ]
                ]
            ]);
    }

    public function testGetInvalidToken()
    {
        $this->seed([UserSeeder::class]);
        $this->get('/api/users/current', ['Authorization' => 'salah'])
            ->assertStatus(401)
            ->assertJson([
                "errors" => [
                    "message" => [
                        "unauthorized"
                    ]
                ]
            ]);
    }

    public function testUpdatePasswordSuccess()
    {
        $this->seed([UserSeeder::class]);
        $oldPassword = User::where('username', 'test')->first();
        $this->patch('/api/users/current',
            ['password' => 'baru'],
            ['Authorization' => 'test']
        )->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => 'test',
                    'name' => 'test'
                ]
            ]);

        $newPassword = User::where('username', 'test')->first();
        self::assertNotEquals($oldPassword->password, $newPassword->password);
    }

    public function testUpdateNameSuccess()
    {
        $this->seed([UserSeeder::class]);
        $oldUser = User::where('username', 'test')->first();

        $response = $this->patch(
            '/api/users/current',
            ['name' => 'Eko'],
            ['Authorization' => 'test']
        );

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => 'test',
                    'name' => 'Eko'
                ]
            ]);

        $newUser = User::where('username', 'test')->first();
        self::assertEquals('Eko', $newUser->name);
        self::assertEquals($oldUser->password, $newUser->password);
        self::assertNotEquals($oldUser->name, $newUser->name);
    }

    public function testUpdateFailed()
    {
        $this->seed([UserSeeder::class]);
        $this->patch('/api/users/current',
            ['name' => str_repeat('Eko', 100)],
            ['Authorization' => 'test'],
        )->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'name' => ['The name field must not be greater than 100 characters.']
                ]
            ]);
    }

    public function testLogoutSuccess()
    {
        $this->seed([UserSeeder::class]);
        $this->delete(uri: '/api/users/logout', headers: ['Authorization' => 'test'])
            ->assertStatus(200)
            ->assertJson([
                'data' => true,
            ]);

        $user = User::where('username', 'test')->first();
        self::assertNull($user->token);
    }

    public function testLogoutFailed()
    {
        $this->seed([UserSeeder::class]);
        $this->delete(uri: '/api/users/logout', headers: ['Authorization' => 'salah'])
            ->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'unauthorized'
                    ]
                ]
            ]);
    }
}
