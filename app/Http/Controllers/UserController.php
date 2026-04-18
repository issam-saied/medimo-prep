<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use App\Http\Requests\StoreUserRequest;

use App\Models\User;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(ListUserRequest $request)
    {
        $data = $request->validated();
        $users = $this->userService->getFilteredUser($data);

        // use collection to transform the collection of users using the UserResource
        return UserResource::collection($users);
    }

    public function show($id)
    {
        //no collection method is needed here since we are only returning a single user,
        //so we can directly return the UserResource for the found user
        return new UserResource(User::findOrFail($id));
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();

        $user = $this->userService->create($data);

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateUserRequest $request, $id)
    {
        $data = $request->validated();

        $user = $this->userService->update($id, $data);

        return new UserResource($user);
    }
}
