<?php

namespace App\Services;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function create(array $data): User
    {
        DB::beginTransaction();

        try {
            $user = $this->createUser($data);

            $data['user_id'] = $user->id;
            $this->createProfile($data);

            DB::commit();
        } catch(\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $user;
    }

    protected function createUser(array $data): User
    {
        return User::create([
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ]);
    }

    protected function createProfile(array $data): Profile
    {
        return Profile::create([
            'user_id' => $data['user_id'],
            'last_name' => $data['last_name'],
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name']
        ]);
    }

    public function update(User $user, array $data): bool
    {
        return $user->profile->update($data);
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }
}
