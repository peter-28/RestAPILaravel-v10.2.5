<?php

namespace Tests\Feature;

use App\Models\Contact;
use Database\Seeders\ContactSeeder;
use Database\Seeders\SearchSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ContactTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testCreateSuccess(): void
    {
        $this->seed([UserSeeder::class]);
        $this->post('/api/contacts', [
            'first_name' => 'Peter',
            'last_name' => '89',
            'email' => "peter@gmail.com",
            'phone' => "0123456789"
        ], [
            'Authorization' => 'test'
        ])->assertStatus(201)
            ->assertJson([
                'data' => [
                    'first_name' => 'Peter',
                    'last_name' => '89',
                    'email' => "peter@gmail.com",
                    'phone' => "0123456789"
                ],
            ]);
    }

    public function testCreateFailed(): void
    {
        $this->seed([UserSeeder::class]);
        $this->post('/api/contacts', [
            'first_name' => '',
            'last_name' => '89',
            'email' => "peter",
            'phone' => "0123456789"
        ], [
            'Authorization' => 'test'
        ])->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'first_name' => ['The first name field is required.'],
                    'email' => ['The email field must be a valid email address.'],
                ],
            ]);
    }

    public function testCreateUnauthorized(): void
    {
        $this->seed([UserSeeder::class]);
        $this->post('/api/contacts', [
            'first_name' => 'Peter',
            'last_name' => '89',
            'email' => "peter@gmail.com",
            'phone' => "0123456789"
        ], [
            'Authorization' => 'salah'
        ])->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => ['unauthorized']
                ],
            ]);
    }

    public function testGetSuccess(): void
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->get('/api/contacts/' . $contact->id, ['Authorization' => 'test']
        )->assertStatus(200)
            ->assertJson([
                'data' => [
                    'first_name' => $contact->first_name,
                    'last_name' => $contact->last_name,
                    'email' => $contact->email,
                    'phone' => $contact->phone
                ]
            ]);
    }

    public function testGetFailed(): void
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->get('/api/contacts/' . ($contact->id + 1), ['Authorization' => 'test']
        )->assertStatus(404)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'not found.'
                    ]
                ]
            ]);
    }

    public function testContactOtherUser()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->get('/api/contacts/' . $contact->id, ['Authorization' => 'admin']
        )->assertStatus(404)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'not found.'
                    ]
                ]
            ]);
    }

    public function testContactUpdateSuccess(): void
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->put('/api/contacts/' . $contact->id, [
            'first_name' => 'admin',
            'last_name' => 'admin',
            'email' => "admin@gmail.com",
            'phone' => "0123456789"
        ], ['Authorization' => "test"]
        )->assertStatus(200)
            ->assertJson([
                'data' => [
                    'first_name' => 'admin',
                    'last_name' => 'admin',
                    'email' => "admin@gmail.com",
                    'phone' => "0123456789"
                ]
            ]);
    }

    public function testContactUpdateFailed(): void
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->put('/api/contacts/' . $contact->id + 2, [
            'first_name' => 'admin',
            'last_name' => 'admin',
            'email' => "admin@gmail.com",
            'phone' => "0123456789"
        ], ['Authorization' => "test"]
        )->assertStatus(404)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'not found.'
                    ]
                ]
            ]);
    }

    public function testContactUpdateValidateErrors(): void
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->put('/api/contacts/' . $contact->id, [
            'first_name' => '',
            'last_name' => 'admin',
            'email' => "admin@gmail.com",
            'phone' => "0123456789"
        ], ['Authorization' => "test"]
        )->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'first_name' => ['The first name field is required.']
                ]
            ]);
    }

    public function testContactDeleteSuccess(): void
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->delete('/api/contacts/' . $contact->id, [], ['Authorization' => "test"])
            ->assertStatus(200)
            ->assertJson([
                "data" => true
            ]);
    }

    public function testContactDeleteNotFound(): void
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->delete('/api/contacts/' . $contact->id + 4, [], ['Authorization' => "test"])
            ->assertStatus(404)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'not found.'
                    ]
                ]
            ]);
    }

    public function testSearchByFirstName(): void
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);

        $response = $this->get('/api/contacts?name=first', ['Authorization' => "test"])
            ->assertStatus(200)
            ->json();

        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertEquals(10, count($response['data']));
        self::assertEquals(20, $response['meta']['total']);
    }

    public function testSearchByLastName(): void
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);

        $response = $this->get('/api/contacts?name=last', ['Authorization' => "test"])
            ->assertStatus(200)
            ->json();

        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertEquals(10, count($response['data']));
        self::assertEquals(20, $response['meta']['total']);
    }

    public function testSearchByEmail(): void
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);

        $response = $this->get('/api/contacts?email=test', ['Authorization' => "test"])
            ->assertStatus(200)
            ->json();

        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertEquals(10, count($response['data']));
        self::assertEquals(20, $response['meta']['total']);
    }

    public function testSearchByPhone(): void
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);

        $response = $this->get('/api/contacts?phone=0123456789', ['Authorization' => "test"])
            ->assertStatus(200)
            ->json();

        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertEquals(10, count($response['data']));
        self::assertEquals(20, $response['meta']['total']);
    }

    public function testSearchWithPage(): void
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);

        $response = $this->get('/api/contacts?size=5&page=2', ['Authorization' => "test"])
            ->assertStatus(200)
            ->json();

        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertEquals(5, count($response['data']));
        self::assertEquals(20, $response['meta']['total']);
        self::assertEquals(2, $response['meta']['current_page']);
    }

    public function testSearchNotFound(): void
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);

        $response = $this->get('/api/contacts?name=tidakada', ['Authorization' => "test"])
            ->assertStatus(200)
            ->json();

        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertEquals(0, count($response['data']));
        self::assertEquals(0, $response['meta']['total']);
    }
}
