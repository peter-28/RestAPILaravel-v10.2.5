<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Contact;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contact = Contact::query()->limit(1)->first();
        Address::create([
            'street' => 'test',
            'city' => 'test',
            'province' => "test",
            'country' => "test",
            'postal_code' => "222222",
            'contact_id' => $contact->id
        ]);
    }
}
