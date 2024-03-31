<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRoleEnum;
use App\Enums\UserTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Controllers\HelperTrait;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\Patient;
use App\Models\User;

class AuthController extends Controller
{
    use \App\Traits\HelperTrait;

    public function register(RegisterUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = \Hash::make($data['password']);
        $data['role'] = UserTypeEnum::Patient;
        $data['is_active'] = 1;
        $data['image_path'] = $this->uploadImages($data['image_path']);

        $user = User::create($data);
        $user->accessToken = $user->createToken('restaurant-app')->accessToken;
        $patient = Patient::create($data + ['id' => $user->id]);

        \Auth::login($user);
        return response()->json(['user' => $user]);
    }

    public function login(LoginUserRequest $request)
    {
        $data = $request->validated();

        $user = User::whereEmail($data['email'])->first();
        if (auth()->attempt($data)) {
            auth()->login($user);
            $user->accessToken = $user->createToken('restaurant-app')->accessToken;
            return response()->json(['user' => $user]);
        } else {
            return response()->json(['error' => "email or password is invalid."]);
        }
    }

    public function editProfile(UpdateProfileRequest $request)
    {
        $data = $request->validated();
        $user = User::whereId($data['patient_id'])->with('patient')->firstOrFail();
        $patient = Patient::whereId($user->id)->firstOrFail();

        if (isset($data['image_path'])) {
            $data['image_path'] = $this->uploadImages($data['image_path']);
        } else {
            $data['image_path'] = $user->patient->image_path;
        }

        $user->update($data);
        $patient->update($data);
        return response()->json(['user' => $user]);
    }

    public function logout()
    {
        $user = auth()->user();
        auth()->logout($user);
        return response()->json(['message' => "User Is Logout."]);
    }


}
