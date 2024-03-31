<?php

namespace App\Http\Controllers\WebsiteControllers;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Specialist;
use App\Traits\RegisterTrait;
use Illuminate\Http\Request;

class WebHomeController extends Controller
{
    use RegisterTrait;

    public function index()
    {
        $specialist = Specialist::paginate(8);
        $doctors = Doctor::inRandomOrder()->limit(10)->get();
        $clinic = Clinic::paginate(8);
        return view('website.index')->with([
            'specialists' => $specialist,
            'doctors' => $doctors,
            'clinics' => $clinic,
        ]);
    }

    public function patientRegister()
    {
        return view('website.auth.patientRegister');
    }

    public function doctorRegister(Request $request)
    {
        if ($request->isMethod('Post')) {
            $this->registerDoctor($request, 2);
            return redirect()->route('getIndex');
        } else {
            $clinic = Clinic::all();
            $specialist = Specialist::all();
            return view('website.auth.doctorRegister')->with(['clinic' => $clinic, 'specialist' => $specialist]);
        }
    }

    public function login()
    {
        return view('website.auth.login');
//        return view('adminlte::auth.login');
    }
}
