<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatusEnum;
use App\Enums\UserTypeEnum;
use App\Models\Appointment;
use App\Models\BlockedPatient;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Specialist;
use App\Models\User;
use App\Rules\MatchOldPassword;
use App\Traits\HelperTrait;
use App\Traits\RegisterTrait;
use App\Traits\UploadTrait;
use Carbon\Carbon;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorController extends Controller
{
    use UploadTrait, RegisterTrait, HelperTrait;

    public function index()
    {
        $data = User::whereRole(UserTypeEnum::Doctor)->get();
        if (session()->has('success')) {
            return view('admin.doctor.index')->with(['data' => $data, "success" => "success"]);
        }
        if (session()->has('update')) {
            return view('admin.doctor.index')->with(['data' => $data, "update" => "update"]);
        }
        if (session()->has('status')) {
            return view('admin.doctor.index')->with(['data' => $data, "status" => "status"]);
        }
        return view('admin.doctor.index')->with(['data' => $data]);
    }

    public function create()
    {
        $clinic = Clinic::all();
        $specialist = Specialist::all();
        return view('admin.doctor.add')->with(['clinic' => $clinic, 'specialist' => $specialist]);
    }

    public function store(Request $request)
    {
        $this->registerDoctor($request, 1);
        return redirect()->route('indexDoctor')->with(['success' => 'success']);
    }

    public function edit($id)
    {
        $data = User::find($id);
        $clinic = Clinic::all();
        $specialist = Specialist::all();

        if ($data) {
            return view('admin.doctor.edit')->with([
                'data' => $data,
                'specialist' => $specialist,
                'clinic' => $clinic]);
        } else abort(404);
    }

    public function update(Request $request)
    {
        $this->updateDoctor($request);
        return redirect()->route('indexDoctor')->with(['update' => "update"]);
    }

    public function changeStatus($id)
    {

        $doctor = Doctor::find($id);
        if ($doctor) {
            $doctor->is_active == 1 ? $doctor->is_active = 0 : $doctor->is_active = 1;
            $doctor->save();
            return redirect()->route('indexDoctor')->with(['status' => "status"]);
        } else abort(404);
    }

    public function doctorAppointments(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            if ($this->isDoctor($user->id)) {
                $patientCount = Appointment::distinct()->whereDoctorId($user->id)->get(['doctor_id', 'patient_id'])->count();
                $today = Carbon::now()->format('Y-m-d');
                $appointments = Appointment::whereDoctorId($user->id)->where('date', '>', $today)->get();

                $yesterday = Carbon::yesterday()->format('Y-m-d');
                $todayAppointmentsCount = Appointment::where('date', '=', $today)->whereDoctorId($user->id)->count();
                $pendingAppointments = Appointment::whereStatus(AppointmentStatusEnum::Pending)->whereDoctorId($user->id)->count();

                $todayAppointments = Appointment::where('date', '=', $today)->whereDoctorId($user->id)->get();
                return view('website.Doctor.index')->with(['appointments' => $appointments, 'todayAppointments' => $todayAppointments,
                    'data' => [
                        'patientCount' => $patientCount,
                        'todayAppointments' => $todayAppointmentsCount,
                        'today' => $today,
                        'pendingAppointments' => $pendingAppointments,
                        'yesterday' => $yesterday
                    ]]);
            } else abort(403);
        } else return redirect()->route('getLogin');
    }

    public function confirmAppointment($id)
    {
        $user = Auth::user();
        if ($user) {
            if ($this->isDoctor($user->id)) {
                if ($this->thisAppointmentForThisDoctor($id, $user->id)) {
                    $this->confirmApp($id);
                    return redirect()->back();
                } else abort(403);
            } else abort(403);
        } else return redirect()->route('getLogin');
    }

    public function cancelAppointment($id)
    {
        $user = Auth::user();
        if ($user) {
            if ($this->isDoctor($user->id)) {
                if ($this->thisAppointmentForThisDoctor($id, $user->id)) {
                    $this->cancelApp($id);
                    return redirect()->back();
                } else abort(403);
            } else abort(403);
        } else return redirect()->route('getLogin');
    }

    public function pendingAppointments(Request $request)
    {

        $user = Auth::user();
        if ($user) {
            if ($this->isDoctor($user->id)) {
                $pendingAppointments = Appointment::whereDoctorId($user->id)->whereStatus(AppointmentStatusEnum::Pending)->get();
                if ($pendingAppointments->count() > 0)
                    return view('website.Doctor.pendingAppointment')->with(['pendingAppointments' => $pendingAppointments]);
                else
                    return redirect()->route('doctorAppointments');
            } else abort(403);
        } else return redirect()->route('getLogin');
    }

    public function patientProfile($id)
    {
        $user = Auth::user();
        if ($user) {
            if ($this->isDoctor($user->id)) {
                if ($this->thisAppointmentForThisDoctor($id, $user->id)) {
                    $appointment = Appointment::whereId($id)->first();
                    $patient = Patient::whereId($appointment->patient_id)->first();
                    $patientAppointments = Appointment::wherePatientId($patient->id)->whereDoctorId($user->id)->get();
                    $yesterday = Carbon::yesterday()->format('Y-m-d');
                    return view('website.Doctor.patientProfile')->with(['patientAppointments' => $patientAppointments, 'patient' => $patient, 'yesterday' => $yesterday]);
                } elseif ($this->thisPatientForThisDoctor($id, $user->id)) {
                    $patient = Patient::whereId($id)->first();
                    $patientAppointments = Appointment::wherePatientId($patient->id)->whereDoctorId($user->id)->get();
                    $yesterday = Carbon::yesterday()->format('Y-m-d');
                    return view('website.Doctor.patientProfile')->with(['patientAppointments' => $patientAppointments, 'patient' => $patient, 'yesterday' => $yesterday]);
                } else    abort(403);
            } else abort(403);
        } else return redirect()->route('getLogin');
    }

    public function getAppointment($id)
    {
        $user = Auth::user();
        if ($user) {
            if ($this->isDoctor($user->id)) {
                if ($this->thisAppointmentForThisDoctor($id, $user->id)) {
                    $appointment = Appointment::whereId($id)->first();
                    $yesterday = Carbon::yesterday()->format('Y-m-d');
                    return view('website.Doctor.singleAppointment')->with(['appointment' => $appointment, 'yesterday' => $yesterday]);
                } else abort(403);
            } else abort(403);
        } else return redirect()->route('getLogin');
    }

    public function getCalenderData(Request $request)
    {
//        if ($request->ajax()) {
        $user = Auth::user();
        if ($user) {
            $start_date = $request->start;
            $new_start_date = date("Y-m-d", strtotime($start_date));
            $end_date = $request->end;
            $new_end_date = date("Y-m-d", strtotime($end_date));

            $appointments = Appointment::where('date', '>=', $new_start_date)
                ->where('date', '<=', $new_end_date)
                ->whereDoctorId($user->id)
                ->get();
            $response = array();
            foreach ($appointments as $appointment) {
                $date = $appointment->date;
                $start_time = $appointment->start_time;
                $finish_time = $appointment->end_time;
                $title = $appointment->patient->user->name . " || " . AppointmentStatusEnum::getKey($appointment->status);
                $start = date('Y-m-d H:i:s', strtotime("$date $start_time"));
                $end = date('Y-m-d H:i:s', strtotime("$date $finish_time"));
                $allDay = false;
                $full_title = $title . " || " . date('h:i a', strtotime("$start_time")) .
                    " To : " . date('h:i a', strtotime("$finish_time"));
                $id = $appointment->id;
                $status = $appointment->status;

                $response[] = array(
                    'id' => $id,
                    'title' => $title,
                    'start' => $start,
                    'end' => $end,
                    'allDay' => $allDay,
                    //                    'url' => $url,
                    'backgroundColor' => $this->getColorByStatus($status),
                    'borderColor' => $this->getColorByStatus($status),
                    'textColor' => '#000',
                    'color  ' => $this->getColorByStatus($status),
                    'status' => $status,
                    'full_title' => $full_title,
                );
            }
            return response()->json($response);
        } else return redirect()->route('getLogin');
    }

    public function getCalender()
    {
        return view('website.Doctor.calender');
    }

    public function blockPatient($id)
    {
        $user = Auth::user();
        if ($user) {
            if ($this->isDoctor($user->id)) {
                if ($this->thisPatientForThisDoctor($id, $user->id)) {
                    $validate = BlockedPatient::whereDoctorId($user->id)->wherePatientId($id)->first();
                    if (!$validate) {
                        $blockedPatient = new BlockedPatient();
                        $blockedPatient->patient_id = $id;
                        $blockedPatient->doctor_id = $user->id;
                        $blockedPatient->save();
                    }
                    return redirect()->route('getBlockedPatient');
                } else abort(403);
            } else abort(403);
        } else return redirect()->route('getLogin');
    }

    public function unBlockPatient($id)
    {
        $user = Auth::user();
        if ($user) {
            if ($this->isDoctor($user->id)) {
                if ($this->thisPatientForThisDoctor($id, $user->id)) {
                    BlockedPatient::wherePatientId($id)->whereDoctorId($user->id)->delete();
                    return redirect()->route('getBlockedPatient');
                } else abort(403);
            } else abort(403);
        } else return redirect()->route('getLogin');
    }

    public function getBlockedPatient()
    {
        $user = Auth::user();
        if ($user) {
            if ($this->isDoctor($user->id)) {
                $blockedPatients = BlockedPatient::whereDoctorId($user->id)->distinct()->get();
                $patients = array();
                foreach ($blockedPatients as $block) {
                    $row = Patient::whereId($block->patient_id)->first();
                    array_push($patients, $row);
                }
                return view('website.Doctor.blockedPatient')->with(['blockedPatients' => $patients]);
            }
            abort(403);
        } else return redirect()->route('getLogin');
    }

    public function getPatients()
    {
        $user = Auth::user();
        if ($user) {
            if ($this->isDoctor($user->id)) {
                $doctorPatients = Appointment::whereDoctorId($user->id)->distinct()->get(['doctor_id', 'patient_id']);
                $patients = array();
                foreach ($doctorPatients as $row) {
                    $row = Patient::whereId($row->patient_id)->first();
                    array_push($patients, $row);
                }
                return view('website.Doctor.myPatients')->with(['patients' => $patients]);
            }
            abort(403);
        } else return redirect()->route('getLogin');
    }

    public function getClinicInfo()
    {
        $user = Auth::user();
        if ($this->isDoctor($user->id)) {
            $clinic = Clinic::whereId($user->doctor->clinic_id)->first();

            return view('website.Doctor.clinic')->with(['clinic' => $clinic]);
        } else abort(403);
    }

    public function clinicSave(Request $request)
    {
        $user = Auth::user();
        if ($this->isDoctor($user->id)) {
            $request->validate([
                'name' => ['required', 'string'],
                'location' => ['required', 'string'],
                'mobile' => ['required', 'string'],
                'open_time' => ['required', 'date_format:H:i:s'],
                'time_end' => ['date_format:H:i:s', 'after:open_time'],
            ]);

            $clinic = Clinic::whereId($user->doctor->clinic_id)->first();
            $clinic->name = $request->name;
            $clinic->location = $request->location;
            $clinic->mobile = $request->mobile;
            $clinic->open_time = $request->open_time;
            $clinic->close_time = $request->close_time;
            $clinic->save();
            return redirect()->route('doctorAppointments');
        } else abort(403);
    }

    public function changeClinicStatus()
    {
        $user = Auth::user();
        if ($this->isDoctor($user->id)) {
            $clinic = Clinic::whereId($user->doctor->clinic_id)->first();
            $clinic->is_open == 1 ? $clinic->is_open = 0 : $clinic->is_open = 1;
            $clinic->save();
            return redirect()->route('doctorAppointments');
        } else abort(403);
    }

    public function profileSetting()
    {
        $user = Auth::user();
        $user->doctor->session_duration = $user->doctor->session_duration[3] . $user->doctor->session_duration[4];
        return view('website.Doctor.profileSetting')->with(['user' => $user]);
    }

    public function doctorSave(Request $request)
    {
        $session_duration = $this->getSessionDuration($request->session_duration);
        $request->validate([
            'name' => ['required', 'string'],
            'mobile' => ['required', 'string'],
        ]);
        $doctor = Auth::user();
        $doctor->name = $request->name;
        $doctor->save();

        $doctorInfo = Doctor::where('id', $doctor->id)->first();;
        $doctorInfo->mobile = $request->mobile;
        if ($request->has('session_duration')) {
            $today = Carbon::now()->format('Y-m-d');
            $updated_at = Carbon::parse($doctorInfo->updated_at)->format('Y-m-d');
            if ($today == $updated_at) {

                if (date('i', strtotime($doctorInfo->old_session_duration)) != date('i', strtotime($doctorInfo->session_duration))) {

                    return back()->withErrors(['session_duration' => "You can't update session duration before 24 hours"]);
                } else {
                    $doctorInfo->old_session_duration = $doctorInfo->session_duration;
                    $doctorInfo->session_duration = $session_duration;
                }
            } else {
                $doctorInfo->old_session_duration = $doctorInfo->session_duration;
                $doctorInfo->session_duration = $session_duration;
            }

        }
        $image = $this->uploadImage($request);
        $old_path = $doctorInfo->image_path;
        $doctorInfo->image_path = $image == null ? $old_path : $image;
        $doctorInfo->save();
        return redirect()->route('doctorAppointments');
    }

    public function changePassword(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'oldPassword' => ['required', new MatchOldPassword, 'min:8'],
                'password' => 'required|confirmed|min:8'
            ]);

            User::find(auth()->user()->id)->update(['password' => Hash::make($request->password)]);
            Auth::logout();
            return redirect()->route('getLogin');

        } else   return view('website.Doctor.changePassword');
    }
}
