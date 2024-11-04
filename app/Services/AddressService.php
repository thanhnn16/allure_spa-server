<?php

namespace App\Services;

use App\Models\Address;

class AddressService
{
    public function create(array $data): Address
    {
        // If this is set as default, unset all other default addresses
        if (isset($data['is_default']) && $data['is_default']) {
            Address::where('user_id', $data['user_id'])
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        return Address::create($data);
    }

    public function update(Address $address, array $data): Address
    {
        // If this is set as default, unset all other default addresses
        if (isset($data['is_default']) && $data['is_default']) {
            Address::where('user_id', $address->user_id)
                ->where('id', '!=', $address->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $address->update($data);
        return $address->fresh();
    }

    public function delete(Address $address): bool
    {
        return $address->delete();
    }
}
