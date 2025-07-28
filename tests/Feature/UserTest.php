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
            'name' => 'peter-28',
        ])
            ->assertStatus(201)
            ->assertJson([
                'data' => [
                    'username' => 'peter',
                    'name' => 'peter-28',
                ],
            ]);
    }

    public function testRegisterFailed(): void
    {
        $this->post('/api/users', [
            'username' => '',
            'password' => '',
            'name' => '',
        ])
            ->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'username' => ['This username field is required.'],
                    'password' => ['This password field is required.'],
                    'name' => ['This name field is required.'],
                ],
            ]);
    }

    public function testRegisterUsernameAlreadyExists(): void
    {
        $this->testRegisterSuccess();
        $this->post('/api/users', [
            'username' => 'peter',
            'password' => 'rahasia',
            'name' => 'peter-28',
        ])
            ->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'username' => ['username already exists'],
                ],
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

    public function TestLoginFailedUsernameNotFound()
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

    public function TestLoginFailedPasswordWrong()
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
                'data' => [
                    'username' => 'test',
                    'name' => 'test',
                ],
            ]);
    }

    public function testGetAnauthorized()
    {
        $this->seed([UserSeeder::class]);
        $this->get('/api/users/current')
            ->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'messages' => ['Unauthorized'],
                ],
            ]);
    }

    public function testGetInvalidToken()
    {
        $this->seed([UserSeeder::class]);
        $this->get('/api/users/current', ['Authorization' => 'salah'])
            ->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'messages' => ['Unauthorized'],
                ],
            ]);
    }

    public function testUpdatePasswordSuccess()
    {
        $this->seed([UserSeeder::class]);
        $oldPassword = User::where('username', 'test')->first();
        $this->patch(
            '/api/users/current',
            [
                'password' => 'baru',
            ],
            [
                'Authorization' => 'test',
            ],
        )
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => 'test',
                    'name' => 'test',
                ],
            ]);

        $newPassword = User::where('username', 'test')->first();
        self::assertNotEquals($oldPassword->password, $newPassword->password);
    }

    public function testUpdateNameSuccess()
    {
        $this->seed([UserSeeder::class]);
        $oldPassword = User::where('username', 'test')->first();
        $this->patch(
            '/api/users/current',
            [
                'name' => 'Eko',
            ],
            [
                'Authorization' => 'test',
            ],
        )
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => 'test',
                    'name' => 'Eko',
                ],
            ]);

        $newPassword = User::where('username', 'test')->first();
        self::assertNotEquals($oldPassword->name, $newPassword->name);
    }

    public function testUpdateFailed()
    {
        $this->seed([UserSeeder::class]);
        $this->patch(
            '/api/users/current',
            [
                'name' => 'EkoEkoEkoEkoEkoEkoEkoEkoEkoEkoEkoEkoEkoEkoEkoEkoEkoEkoEkoEkoEkoEkoEkoEkoEkoEko',
            ],
            [
                'Authorization' => 'test',
            ],
        )
            ->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'name' => ['The name field must not be greater than 255 characters.'],
                ],
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
                    'messages' => ['Unauthorized'],
                ],
            ]);
    }
}
