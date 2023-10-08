<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\ChangeAvatarUserRequest;
use App\Http\Resources\User\MinimalInfoUserResource;
use Modules\File\Transformers\FileResource;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Services\UserService;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('profile')->get();

        return MinimalInfoUserResource::collection($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $user = $this->userService->create($data);

        return MinimalInfoUserResource::make($user);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return UserResource::make($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();
        $updated = $this->userService->update($user, $data);

        $response = $updated
            ? UserResource::make($user)
            : response()->json(['error' => 'Пользователь не найден'], 404);

        return $response;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $deleted = $this->userService->delete($user);

        $response = $deleted
            ? response()->json(null, 204)
            : response()->json(['error' => 'Пользователь не найден'], 404);

        return $response;
    }

    /**
     * Change users avatar
     */
    public function changeAvatar(ChangeAvatarUserRequest $request, User $user): FileResource
    {
        $avatar = $request->file('avatar');
        $file = $this->userService->changeAvatar($user, $avatar);

        return FileResource::make($file);
    }
}
