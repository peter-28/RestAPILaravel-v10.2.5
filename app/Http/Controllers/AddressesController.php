<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressesCreateRequest;
use App\Http\Requests\AddressesUpdateRequest;
use App\Http\Resources\AddressesResource;
use App\Models\Address;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressesController extends Controller
{
    private function getContact(User $user, int $idContact): Contact
    {
        $contact = Contact::where('user_id', $user->id)->where('id', $idContact)->first();
        if (!$contact) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    "message" => [
                        "not found."
                    ]
                ]
            ])->setStatusCode(404));
        }

        return $contact;
    }

    private function getAddress(Contact $contact, int $idAddress): Address
    {
        $address = Address::where('id', $idAddress)->where('contact_id', $contact->id)->first();
        if (!$address) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    "message" => [
                        "not found.",
                    ]
                ]
            ])->setStatusCode(404));
        }
        return $address;
    }

    public function create(int $idContact, AddressesCreateRequest $request): JsonResponse
    {
        $user = Auth::user();
        $contact = $this->getContact($user, $idContact);

        $data = $request->validated();
        $address = new Address($data);
        $address->contact_id = $contact->id;
        $address->save();

        return (new AddressesResource($address))->response()->setStatusCode(201);
    }

    public function get(int $idContact, int $idAddress): AddressesResource
    {
        $user = Auth::getUser();
        $contact = $this->getContact($user, $idContact);
        $address = $this->getAddress($contact, $idAddress);

        return new AddressesResource($address);
    }

    public function update(int $idContact, int $idAddress, AddressesUpdateRequest $request): AddressesResource
    {
        $user = Auth::user();

        $contact = $this->getContact($user, $idContact);

        $address = $this->getAddress($contact, $idAddress);
        $data = $request->validated();
        $address->fill($data);
        $address->save();

        return new AddressesResource($address);
    }

    public function delete(int $idContact, int $idAddress): JsonResponse
    {
        $user = Auth::user();
        $contact = $this->getContact($user, $idContact);
        $address = $this->getAddress($contact, $idAddress);
        $address->delete();
        return response()->json(["data" => true])->setStatusCode(200);
    }
}
