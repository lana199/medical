<?php

namespace App\Http\Controllers;

use App\Models\Specialist;
use App\Traits\UploadTrait;
use Illuminate\Http\Request;
use Str;

class SpecialistController extends Controller
{
    use UploadTrait;

    public function index()
    {
        $data = Specialist::all();
        if (session()->has('success')) {
            return view('admin.specialist.index')->with(['data' => $data, "success" => "success"]);
        }
        if (session()->has('update')) {
            return view('admin.specialist.index')->with(['data' => $data, "update" => "update"]);
        }
        if (session()->has('status')) {
            return view('admin.specialist.index')->with(['data' => $data, "status" => "status"]);
        }
        return view('admin.specialist.index')->with(['data' => $data]);
    }

    public function create()
    {
        return view('admin.specialist.add');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'unique:specialists'],
        ]);

        $specialist = new Specialist();
        $specialist->name = $request->name;
        if ($request->has('image')) {
            $image = $request->file('image');
            $name = Str::slug($request->input('name')) . '_' . time();
            $folder = '/uploads/images/';
            $filePath = $folder . $name . '.' . $image->getClientOriginalExtension();
            $this->uploadOne($image, $folder, 'public', $name);
            $specialist->image_path = $filePath;
        }

        $specialist->save();

        return redirect()->route('indexSpecialist')->with(["success" => "success"]);

    }

    public function edit($id)
    {
        $data = Specialist::find($id);
        if ($data) {
            return view('admin.specialist.edit')->with(['data' => $data]);
        } else abort(404);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'numeric',
            'name' => ['required', 'unique:specialists'],
        ]);
        $specialist = Specialist::find($request->id);
        $specialist->name = $request->name;
        $specialist->save();

        return redirect()->route('indexSpecialist')->with(['update' => "update"]);
    }

    public function changeStatus($id)
    {
        $specialist = Specialist::find($id);
        if ($specialist) {
            $specialist->is_active ? $specialist->is_active = 0 : $specialist->is_active = 1;
            $specialist->save();
            return redirect()->route('indexSpecialist')->with(['status' => "status"]);
        } else abort(404);
    }
}
