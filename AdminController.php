<?php

namespace App\Http\Controllers;

use App\Enums\UserTypeEnum;
use App\Models\User;
use App\Traits\RegisterTrait;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    use RegisterTrait;

    public function index()
    {
        $data = User::whereRole(UserTypeEnum::Admin)->get();

        if (session()->has('success')) {
            return view('admin.adminUsers.index')->with(['data' => $data, "success" => "success"]);
        }
        if (session()->has('update')) {
            return view('admin.adminUsers.index')->with(['data' => $data, "update" => "update"]);
        }
        if (session()->has('status')) {
            return view('admin.adminUsers.index')->with(['data' => $data, "status" => "status"]);
        }
        return view('admin.adminUsers.index')->with(['data' => $data]);
    }

    public function create()
    {
        return view('admin.adminUsers.add');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'=>['required','string'],
            'email'=>['required','email'],
            'password'=>['required','string'],
        ]);
        $this->registerAdmin($request);
        return redirect()->route('indexAdmin')->with(['success' => 'success']);
    }


    public function edit($id)
    {
        $data = User::find($id);
        if ($data) {
            return view('admin.adminUSers.edit')->with(['data' => $data,]);
        }
    }


    public function update(Request $request)
    {
        $this->updateAdmin($request);
        return redirect()->route('indexAdmin')->with(['update' => "update"]);
    }


}
