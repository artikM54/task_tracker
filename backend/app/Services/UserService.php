<?php

namespace App\Services;

use Exception;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Modules\File\Models\File;

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
        } catch(Exception $e) {
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

    public function changeAvatar(User $user, object $avatar): File
    {
        $user->load('profile.avatar');
        $currentAvatar = $user->profile->avatar;

        $path = Storage::disk('private')->put('avatars', $avatar);

        try {
            $user->profile->avatar()->create([
                'filename'=> $avatar->name,
                'path'=> $path,
            ]);

            if ($currentAvatar) {
                $deleted = $this->deleteAvatar($currentAvatar);

                if(! $deleted) {
                    throw new Exception('Ошибка удаления аватара');
                }
            }

            DB::commit();
        } catch(Exception $e) {
            DB::rollBack();
            Storage::delete($path);

            throw $e;
        }

        $user->load('profile.avatar');
        return $user->profile->avatar;
    }

    public function deleteAvatar(File $avatar): bool
    {
        $deleted = $avatar->delete();

        if($deleted) {
            Storage::disk('private')->delete($avatar->path);
        }

        return $deleted;
    }
}
