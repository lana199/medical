<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatusEnum;
use App\Models\Appointment;
use App\Models\BlockedPatient;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\DoctorHoliday;
use App\Models\Patient;
use App\Models\Specialist;
use App\Traits\HelperTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    use HelperTrait;

    public function specialist()
    {
        $specialist = Specialist::whereIsActive(true)->get();
        return response()->json(['specialists' => $specialist]);
    }

    public function doctors()
    {
        $doctors = Doctor::whereIsActive(true)->with('user')->inRandomOrder()->get();
        return response()->json(['doctors' => $doctors]);
    }

    public function clinics()
    {
        $clinic = Clinic::whereIsActive(true)->get();
        return response()->json(['clinics' => $clinic]);
    }

    public function openClinics()
    {
        $clinic = Clinic::whereIsActive(true)->whereIsOpen(true)->get();
        return response()->json(['clinics' => $clinic]);
    }

    public function getDoctorInformation($doctor_id)
    {
        return response()->json([
            'doctor' => Doctor::whereId($doctor_id)->with('user')->first()
        ]);

    }

    public function getPatientInformation($patient_id)
    {
        return response()->json(['patient' => Patient::whereId($patient_id)->with('user')->first()]);

    }

    public function doctorsBySpecialist($specialist_id)
    {
        $doctors = Doctor::whereIsActive(true)->with('user')->whereSpecialistId($specialist_id)->get();
        return response()->json(['doctors' => $doctors]);
    }

    public function doctorsByClinic($clinic_id)
    {
        $doctors = Doctor::whereIsActive(true)->with('user')->whereClinicId($clinic_id)->get();
        return response()->json(['doctors' => $doctors]);
    }

    public function getPatientAppointments($patient_id)
    {
        $enums = AppointmentStatusEnum::getValues();
        $user = Patient::whereId($patient_id)->first();
        $appointments = Appointment::query()->wherePatientId($user->id);


        $appointments = Appointment::wherePatientId($user->id)->with(['doctor.user', 'patient.user'])->orderBy('date',
            'desc')->get();

        return response()->json(['appointments' => $appointments]);
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
                $holiday_times = $this->CreateTimeRange($holiday->start_time, $holiday->end_time, $minutes . ' mins',
                    '24');
            }
            $start_times = Appointment::whereDoctorId($request->doctor_id)->where('status', '!=',
                AppointmentStatusEnum::Canceled)
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
        if ($minutes == 0) {
            $minutes = 60;
        }
        return $minutes;
    }

    public function getDateFormat($date)
    {
        $dateFormat = strtr($date, '/', '-');
        return date("Y-m-d", strtotime($dateFormat));

    }

    public function storeAppointments(Request $request)
    {
        $patient = Patient::whereId($request->patient_id)->first();
        if ($patient) {
            $block = BlockedPatient::whereDoctorId($request->doctor_id)->wherePatientId($patient->id)->first();
            if ($block) {
                $doctor = Doctor::whereId($request->doctor_id)->first();
                return response()->json(['error' => 'pataint is blocked by doctor']);
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
                $appointment->patient_id = $patient->id;
                $appointment->doctor_id = $doctor->id;
                $appointment->start_time = $request->start_time;
                $appointment->end_time = $endTime->format('h:i:s');
                $appointment->save();

                $appointment = Appointment::whereId($appointment->id)->with(['patient', 'doctor'])->first();
                return response()->json(['appointment' => $appointment]);
            }
        }
        return response()->json(['error' => 'pataint is blocked by Admin']);
    }

    public function cancle($id)
    {
        $appointment = Appointment::whereId($id)->first();
        $appointment->status = AppointmentStatusEnum::Canceled;
        $appointment->save();
        return response()->json(['appointment' => $appointment]);
    }
}
