<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatusEnum;
use App\Enums\UserTypeEnum;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Specialist;
use App\Traits\RegisterTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientController extends Controller
{
    use RegisterTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Patient::get();

        return view('admin.patient.index')->with(['data' => $data]);
    }

    public function changeStatus($id)
    {

        $patient = Patient::find($id);
        if ($patient) {
            $patient->is_active == 1 ? $patient->is_active = 0 : $patient->is_active = 1;
            $patient->save();
            return redirect()->route('indexPatient')->with(['status' => "status"]);
        } else abort(404);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string'],
            'age' => ['required', 'integer'],
            'email' => ['required', 'email', 'unique:users'],
            'mobile' => ['required', 'string'],
            'location' => ['required', 'string'],
            'gender' => ['required', 'integer', 'min:1', 'max:2'],
            'password' => ['required', 'string'],
        ]);
        $this->registerPatient($request);
        return redirect()->route('home');

    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Patient $patient
     * @return \Illuminate\Http\Response
     */
    public function show(Patient $patient)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Patient $patient
     * @return \Illuminate\Http\Response
     */
    public function edit(Patient $patient)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Patient $patient
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Patient $patient)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Patient $patient
     * @return \Illuminate\Http\Response
     */
    public function destroy(Patient $patient)
    {
        //
    }

    public function searchBySpecialist(Request $request)
    {
        $specialists = Specialist::all();
        $oldGender = $request->gender;
        $oldSpecialists = $request->specialists;
        if ($oldGender == null) $oldGender = array('0' => 0);
        if ($oldSpecialists == null) $oldSpecialists = array('0' => 0);
        $doctors = Doctor::orderBy('created_at', 'desc')->whereIsActive(1);
        $doctors = $doctors->orderBy('created_at', 'desc');
        if ($request->has('gender')) {
            $doctors->whereIn('gender', $request->gender);
        }
        if ($request->has('specialists')) {
            $doctors->whereIn('specialist_id', $request->specialists);
        }
        $doctors = $doctors->paginate(10);
        return view('website.Patient.searchBySpecialist')->with(['doctors' => $doctors, 'specialists' => $specialists,
            'oldSpecialists' => $oldSpecialists, 'oldGender' => $oldGender]);

    }

    public function bookByDoctor($id)
    {
        $doctor = Doctor::whereId($id)->first();
        $user = \Auth::user();
        if ($user) {
            if ($doctor) {
                if ($user->role == UserTypeEnum::Patient) {
                    return view('website.Patient.bookAppointment')->with(['doctor' => $doctor]);
                } else {
                    return redirect()->route('notBook');
                }
            } else abort(404);
        } else abort(403);
    }

    public function getPatientAppointments($status)
    {
        $enums = AppointmentStatusEnum::getValues();
        $user = Auth::user();
        $appointments = Appointment::query()->wherePatientId($user->id);
        $yesterday = Carbon::yesterday()->format('Y-m-d');
        $lastAppointment = Appointment::orderBy('created_at', 'desc')->first();
        $pendingAppointments = $appointments->whereStatus(AppointmentStatusEnum::Pending)->count();
        $appointments = Appointment::query()->wherePatientId($user->id);
        $confirmedAppointments = $appointments->whereStatus(AppointmentStatusEnum::Confirmed)->count();
        $appointments = Appointment::query()->wherePatientId($user->id);
        $canceledAppointments = $appointments->whereStatus(AppointmentStatusEnum::Canceled)->count();
        $data = ['yesterday' => $yesterday, 'lastAppointment' => $lastAppointment,
            'pendingCount' => $pendingAppointments, 'confirmedCount' => $confirmedAppointments,
            'canceledCount' => $canceledAppointments];

        if (in_array($status, $enums))
            $appointments = Appointment::wherePatientId($user->id)->whereStatus($status)->orderBy('date', 'desc')->get();
        else {
            $appointments = Appointment::wherePatientId($user->id)->orderBy('date', 'desc')->get();
        }
        return view('website.Patient.appointments')->with(['appointments' => $appointments, 'data' => $data]);
    }

    public function cancle($id)
    {
        $app = Appointment::whereId($id)->first();
        $app->status = AppointmentStatusEnum::Canceled;
        $app->save();
        return redirect()->back();
    }
}
