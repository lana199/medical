<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatusEnum;
use App\Enums\UserTypeEnum;
use App\Models\Appointment;
use App\Models\BlockedPatient;
use App\Models\Doctor;
use App\Models\DoctorHoliday;
use App\Models\User;
use App\Traits\HelperTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    use HelperTrait;


    public function index()
    {
        return view('website.Appointments.bookAppointment');
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $patient = \Auth::user();
        if ($patient) {
            $block = BlockedPatient::whereDoctorId($request->doctor_id)->wherePatientId($patient->id)->first();
            if ($block) {
                $doctor = Doctor::whereId($request->doctor_id)->first();
                return view('website.Blocked')->with(['doctor' => $doctor]);
            } else {
                $date = $this->getDateFormat($request->date);
                $doctor = Doctor::whereId($request->doctor_id)->first();
                $time = Carbon::parse($request->start_time);
                $minutes = $this->getMinutes($doctor->session_duration);
                $endTime = $time->addMinutes($minutes);
                $appointment = new Appointment();
                $appointment->date = $date;
                $appointment->desc = $request->desc;
                $appointment->status = AppointmentStatusEnum::Pending;
                $appointment->patient_id = \Auth::user()->id;
                $appointment->doctor_id = $doctor->id;
                $appointment->start_time = $request->start_time;
                $appointment->end_time = $endTime->format('h:i:s');
                $appointment->save();


                return redirect()->route('getSuccessView', [$appointment->id]);
            }
        }
        abort(403);
    }

    public function getSuccessView($id)
    {
        $appointment = Appointment::whereId($id)->first();
        if ($appointment) {
            $user = \Auth::user();
            if ($user->id == $appointment->patient_id) {
                $doctor = Doctor::whereId($appointment->doctor_id)->first();
                $data = [
                    'drName' => $doctor->user->name,
                    'date' => $appointment->date,
                    'from' => date('h:i a', strtotime($appointment->start_time)),
                    'to' => date('h:i a', strtotime($appointment->end_time)),
                    'desc' => $appointment->desc
                ];
                return view('website.Appointments.AppointmentBookedSuccessfully')->with(['data' => $data]);
            } else abort(403);
        } else abort(404);
    }

    public function show(Appointment $appointment)
    {
        //
    }

    public function edit(Appointment $appointment)
    {
        //
    }

    public function update(Request $request, Appointment $appointment)
    {
        //
    }

    public function destroy(Appointment $appointment)
    {
        //
    }

    public function getPaginationDoctors(Request $request)
    {
        if ($request->ajax()) {

            $page = $request->get("page");
            $resultCount = 15;
            $count = Doctor::all()->count();
            if ($resultCount > $count) $resultCount = $count;

            $offset = ($page - 1) * $resultCount;
            $doctors = User::where('name', 'LIKE', '%' . $request->get("term") . '%')->whereRole(UserTypeEnum::Doctor)
                ->orderBy('name')->skip($offset)->take($resultCount)->get(['id', DB::raw('name as text')]);
            global $data;
            foreach ($doctors as $user) {
                $doctor = Doctor::find($user->id);
                if ($doctor) {
                    $is_active = $doctor->is_active;
                    if ($is_active) {
                        $sp = $doctor->specialist->name;
                        $user->text = "Name : " . $user->text . "  || Specialist : " . $sp;
                        $data[] = array(
                            "id" => $user->id,
                            "text" => $user->text,
                        );
                    }
                }
            }

            $endCount = $offset + $resultCount;
            $morePages = $endCount > $count;

            $results = array(
                "results" => $data,
                "pagination" => array(
                    "more" => $morePages
                )
            );

            return response()->json($results);
        }
    }

    public function getDoctorAvailableTimes(Request $request)
    {

        global $response;
        if ($request->doctor_id == '') {
            $response[] = array(
                "id" => 0,
                "text" => "Please select doctor",
                "disabled" => "disabled"
            );
        } elseif ($request->date == '') {
            $response[] = array(
                "id" => 0,
                "text" => "Please select date",
                "disabled" => "disabled"
            );
        } else {
            $doctor = Doctor::whereId($request->doctor_id)->first();
            $open = $doctor->clinic->open_time;
            $close = $doctor->clinic->close_time;
            $minutes = $this->getMinutes($doctor->session_duration);
            $times = $this->CreateTimeRange($open, $close, $minutes . ' mins', '24');
            $date = $this->getDateFormat($request->date);

            $holiday = DoctorHoliday::whereDoctorId($request->doctor_id)->where('date', $date)->first();
            $holiday_times = array();
            if ($holiday) {
                $holiday_times = $this->CreateTimeRange($holiday->start_time, $holiday->end_time, $minutes . ' mins', '24');
            }
            $start_times = Appointment::whereDoctorId($request->doctor_id)->where('status', '!=', AppointmentStatusEnum::Canceled)
                ->where('date', '=', $date)->pluck('start_time')->toArray();
            $dd = array_diff($times, $holiday_times);
            $d = array_diff($dd, $start_times);

            foreach ($d as $value) {
                $response[] = array(
                    'id' => date('h:i:s', strtotime($value)),
                    'text' => date('h:i A', strtotime($value))
                );
            }
        }

        $results = array(
            "results" => $response,
            "pagination" => array(
                "more" => 0
            )
        );
        return response()->json($results);

    }

    public function getMinutes($time)
    {
        $minutes = Carbon::createFromFormat('H:i:s', $time);
        $minutes = $minutes->minute;
        if ($minutes == 0) $minutes = 60;
        return $minutes;
    }

    public function getDateFormat($date)
    {
        $dateFormat = strtr($date, '/', '-');
        return date("Y-m-d", strtotime($dateFormat));

    }
}
