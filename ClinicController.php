<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Doctor;
use Illuminate\Http\Request;

class ClinicController extends Controller
{

    public function getDash()
    {
        return view('admin.index');
    }
    public function index()
    {
        $data = Clinic::all();
        if (session()->has('success')) {
            return view('admin.clinic.index')->with(['data' => $data, "success" => "success"]);
        }
        if (session()->has('update')) {
            return view('admin.clinic.index')->with(['data' => $data, "update" => "update"]);
        }
        if (session()->has('status')) {
            return view('admin.clinic.index')->with(['data' => $data, "status" => "status"]);
        }
        return view('admin.clinic.index')->with(['data' => $data]);
    }

    public function create()
    {
        return view('admin.clinic.add');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'unique:clinics'],
            'mobile' => ['required', 'numeric'],
            'location' => 'required',
            'open_time' => ['required','date_format:H:i'],
            'close_time' => ['required','date_format:H:i','after:open_time'],
        ]);

        $clinic = new Clinic();
        $clinic->name = $request->name;
        $clinic->mobile = $request->mobile;
        $clinic->location = $request->location;
        $clinic->open_time = $request->open_time;
        $clinic->close_time = $request->close_time;
        $clinic->save();

        return redirect()->route('indexClinic')->with(["success" => "success"]);

    }

    public function edit($id)
    {
        $data = Clinic::find($id);
        if ($data) {
            return view('admin.clinic.edit')->with(['data' => $data]);
        } else abort(404);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'numeric',
            'name' => ['required', 'unique:clinics'],
            'mobile' => ['required', 'numeric'],
            'location' => 'required'
        ]);
        $clinic = Clinic::find($request->id);
        $clinic->name = $request->name;
        $clinic->mobile = $request->mobile;
        $clinic->location = $request->location;
        $clinic->save();

        return redirect()->route('indexClinic')->with(['update' => "update"]);
    }

    public function closeOrOpenClinic($id)
    {
        $clinic = Clinic::find($id);
        if ($clinic) {
            $clinic->is_open ? $clinic->is_open = 0 : $clinic->is_open = 1;
            $clinic->save();
            return redirect()->route('indexClinic')->with(['status' => "status"]);
        } else abort(404);
    }

    public function changeStatus($id)
    {
        $clinic = Clinic::find($id);
        if ($clinic) {
            $clinic->is_active ? $clinic->is_active = 0 : $clinic->is_active = 1;
            $clinic->save();
            return redirect()->route('indexClinic')->with(['status' => "status"]);
        } else abort(404);
    }

    public function getOpenClinics()
    {
        $clinics = Clinic::whereIsOpen(true)->orderBy('created_at','desc')->paginate(9);

        return view('website.Clinics.openClinics')->with(['clinics' => $clinics]);
    }

    public function getDoctorsByClinicId($id)
    {
        $doctors = Doctor::whereClinicId($id)->get();
        if ($doctors->count()>0){
            return view('website.Clinics.doctorsByClinic')->with(['doctors' => $doctors]);
        }else abort(404);
    }
}
