<?php

namespace App\Traits;

use App\Enums\AppointmentStatusEnum;
use App\Enums\UserTypeEnum;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;

trait HelperTrait
{
    public function CreateTimeRange($start, $end, $interval, $format = '12')
    {
        $startTime = strtotime($start);
        $endTime = strtotime($end);
        $returnTimeFormat = ($format == '12') ? 'g:i:s A' : 'h:i:s';

        $current = time();
        $addTime = strtotime('+' . $interval, $current);
        $diff = $addTime - $current;

        while ($startTime < $endTime) {
            $times[] = date($returnTimeFormat, $startTime);
            $startTime += $diff;

        }
        $times[] = date($returnTimeFormat, $startTime);
        return $times;
    }

    public function isDoctor($id)
    {
        $user = User::whereId($id)->first();
        if ($user) {
            if ($user->role == UserTypeEnum::Doctor) {
                $doctor = Doctor::whereId($id)->first();
                if ($doctor) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function thisAppointmentForThisDoctor($appointment_id, $doctor_id)
    {

        if (Appointment::whereId($appointment_id)->whereDoctorId($doctor_id)->first()) {
            return true;
        } else {
            return false;
        }
    }

    public function confirmApp($id)
    {
        $app = Appointment::whereId($id)->first();
        $app->status = AppointmentStatusEnum::Confirmed;
        $app->save();
    }

    public function cancelApp($id)
    {
        $app = Appointment::whereId($id)->first();
        $app->status = AppointmentStatusEnum::Canceled;
        $app->save();
    }

    public function getColorByStatus($status)
    {
        if ($status == AppointmentStatusEnum::Pending) {
            return "#ffc107";
        } else {
            if ($status == AppointmentStatusEnum::Confirmed) {
                return "#198754";
            } else {
                if ($status == AppointmentStatusEnum::Canceled) {
                    return "#dc3545";
                }
            }
        }
    }

    public function thisPatientForThisDoctor($patient_id, $doctor_id)
    {

        if (Appointment::wherePatientId($patient_id)->whereDoctorId($doctor_id)->first()) {
            return true;
        } else {
            return false;
        }
    }

    public function uploadImages($image)
    {
        $logo = time() + random_int(0, 999) . '.' . $image->extension();
        $image->move(('images') . '/' . date('d-m-Y'), $logo);
        $temp = '/images/' . date('d-m-Y') . '/' . $logo;
        return $temp;
    }
}
