<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Contact;
use Database\Seeders\AddressSeeder;
use Database\Seeders\ContactSeeder;
use Database\Seeders\UserSeeder;
use Tests\TestCase;

class AddressesTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testCreateAddressSuccess(): void
    {
        $this->seed([UserSeeder::class, ContactSeeder   ::class]);

        $contact = Contact::query()->limit(1)->first();

        $this->post('/api/contacts/' . $contact->id . '/addresses', [
            'street' => 'test',
            'city' => 'test',
            'province' => "test",
            'country' => "test",
            'postal_code' => "222222"
        ], [
            'Authorization' => 'test',
        ])->assertStatus(201)
            ->assertJson([
                'data' => [
                    'street' => 'test',
                    'city' => 'test',
                    'province' => "test",
                    'country' => "test",
                    'postal_code' => "222222"
                ]
            ]);
    }

    public function testCreateAddressFailed(): void
    {
        $this->seed([UserSeeder::class, ContactSeeder   ::class]);

        $contact = Contact::query()->limit(1)->first();

        $this->post('/api/contacts/' . $contact->id . '/addresses', [
            'street' => 'test',
            'city' => 'test',
            'province' => "test",
            'country' => "",
            'postal_code' => "222222"
        ], [
            'Authorization' => 'test',
        ])->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'country' => ['The country field is required.'],
                ]
            ]);
    }

    public function testCreateAddressNotFound(): void
    {
        $this->seed([UserSeeder::class, ContactSeeder   ::class]);

        $contact = Contact::query()->limit(1)->first();

        $this->post('/api/contacts/' . ($contact->id + 1) . '/addresses', [
            'street' => 'test',
            'city' => 'test',
            'province' => "test",
            'country' => "test",
            'postal_code' => "222222"
        ], [
            'Authorization' => 'test',
        ])->assertStatus(404)
            ->assertJson([
                'errors' => [
                    'message' => ['not found.'],
                ]
            ]);
    }

    public function testGetSuccess(): void
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $address = Address::query()->limit(1)->first();

        $this->get('/api/contacts/' . $address->contact_id . '/addresses/' . $address->id, ['Authorization' => 'test']
        )->assertStatus(200)
            ->assertJson([
                'data' => [
                    'street' => 'test',
                    'city' => 'test',
                    'province' => "test",
                    'country' => "test",
                    'postal_code' => "222222",
                ]
            ]);
    }

    public function testGetNotFound(): void
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $address = Address::query()->limit(1)->first();

        $this->get('/api/contacts/' . ($address->contact_id + 1) . '/addresses/' . ($address->id + 1), ['Authorization' => 'test']
        )->assertStatus(404)
            ->assertJson([
                'errors' => [
                    'message' => ['not found.'],
                ]
            ]);
    }

    public function testUpdateSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $address = Address::query()->limit(1)->first();

        $this->put('/api/contacts/' . $address->contact_id . '/addresses/' . $address->id, [
            'street' => 'update',
            'city' => 'update',
            'province' => "update",
            'country' => "update",
            'postal_code' => "333333"
        ], ['Authorization' => 'test']
        )->assertStatus(200)
            ->assertJson([
                'data' => [
                    'street' => 'update',
                    'city' => 'update',
                    'province' => "update",
                    'country' => "update",
                    'postal_code' => "333333"
                ]
            ]);
    }

    public function testUpdateFailed()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $address = Address::query()->limit(1)->first();

        $this->put('/api/contacts/' . $address->contact_id . '/addresses/' . $address->id, [
            'street' => 'test',
            'city' => 'test',
            'province' => "test",
            'country' => "",
            'postal_code' => "222222"
        ], ['Authorization' => 'test']
        )->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'country' => [
                        'The country field is required.'
                    ]
                ]
            ]);
    }

    public function testUpdateNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $address = Address::query()->limit(1)->first();

        $this->put('/api/contacts/' . ($address->contact_id + 1) . '/addresses/' . ($address->id + 1), [
            'street' => 'test',
            'city' => 'test',
            'province' => "test",
            'country' => "test",
            'postal_code' => "222222"
        ], ['Authorization' => 'test']
        )->assertStatus(404)
            ->assertJson([
                'errors' => [
                    'message' => ['not found.'],
                ]
            ]);
    }

    public function testDeleteSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $address = Address::query()->limit(1)->first();

        $this->delete('/api/contacts/' . $address->contact_id . '/addresses/' . $address->id, [], ['Authorization' => 'test'])
            ->assertStatus(200)
            ->assertJson(["data" => true]);
    }

    public function testDeleteNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $address = Address::query()->limit(1)->first();

        $this->delete('/api/contacts/' . ($address->contact_id + 1) . '/addresses/' . $address->id, [], ['Authorization' => 'test'])
            ->assertStatus(404)
            ->assertJson([
                'errors' => [
                    'message' => ['not found.']
                ]
            ]);

    }

    public function testListSuccess(): void
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->get('/api/contacts/' . $contact->id . '/addresses', ['Authorization' => 'test']
        )->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'street' => 'test',
                        'city' => 'test',
                        'province' => "test",
                        'country' => "test",
                        'postal_code' => "222222",
                    ]
                ]
            ]);
    }

    public function testListNotFound(): void
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->get('/api/contacts/' . ($contact->id + 1) . '/addresses/', ['Authorization' => 'test']
        )->assertStatus(404)
            ->assertJson([
                'errors' => [
                    'message' => ['not found.'],
                ]
            ]);
    }
}
