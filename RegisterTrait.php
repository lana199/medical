<?php

namespace App\Traits;

use App\Enums\UserTypeEnum;
use App\Models\Doctor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Str;

trait RegisterTrait
{
    use UploadTrait;

    public function registerDoctor(Request $request, $from)
    {

        $session_duration = $this->getSessionDuration($request->session_duration);
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->role = UserTypeEnum::Doctor;
        $user->save();

        $doctor = new Doctor();
        $doctor->id = $user->id;
        $doctor->mobile = $request->mobile;
        $doctor->gender = $request->gender;
        $doctor->clinic_id = $request->clinic_id;
        $doctor->specialist_id = $request->specialist_id;

        $doctor->session_duration = $session_duration;
        $doctor->old_session_duration = $session_duration;
        $from == 1 ? $doctor->is_active = 1 : $doctor->is_active = 0;
        $doctor->image_path = $this->uploadImage($request);
        $doctor->save();
    }

    public function updateDoctor(Request $request)
    {

        if ($request->has('session_duration'))
        $session_duration = $this->getSessionDuration($request->session_duration);
        $user = User::whereId($request->id)->first();
        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->has('password')) $user->password = bcrypt($request->password);
        $user->save();

        $doctor = Doctor::whereId($user->id)->first();
        $doctor->mobile = $request->mobile;
        $doctor->gender = $request->gender;
        if ($request->has('session_duration')) {
            $today = Carbon::now()->format('Y-m-d');
            $updated_at = Carbon::parse($doctor->updated_at)->format('Y-m-d');
            if ($today == $updated_at) {
                if (date('i', strtotime($doctor->old_session_duration)) != date('i', strtotime($doctor->session_duration))) {
                    return back()->withErrors(['session_duration' => "You can't update session duration before 24 hours"]);
                } else {
                    $doctor->old_session_duration = $doctor->session_duration;
                    $doctor->session_duration = $session_duration;
                }
            } else {
                $doctor->old_session_duration = $doctor->session_duration;
                $doctor->session_duration = $session_duration;
            }

        }
        $image = $this->uploadImage($request);
        $old_path = $doctor->image_path;
        $doctor->clinic_id = $request->clinic_id;
        $doctor->specialist_id = $request->specialist_id;
        $doctor->image_path = $image == null ? $old_path : $image;

        $doctor->save();

    }

    public function registerAdmin(Request $request)
    {
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->role = UserTypeEnum::Admin;
        $user->save();
    }

    public function updateAdmin(Request $request)
    {
        $user = User::whereId($request->id)->first();
        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->has('password')) $user->password = bcrypt($request->password);
        $user->save();

    }

    public function registerPatient(Request $request)
    {

        $user = new User();
        $data=$request->all();
        $data["password"]=bcrypt($request->password);
        $data["role"]=UserTypeEnum::Patient;
        $user=User::create($data);
        $data['image_path'] = $this->uploadImage($request);
        $user->patient()->create($data);
        \Auth::login($user);
    }

    public function uploadImage(Request $request)
    {
        $image_Path = null;
        if ($request->has('image')) {
            $image = $request->file('image');
            $name = Str::slug($request->input('name')) . '_' . time();
            $folder = '/uploads/images/';
            $filePath = $folder . $name . '.' . $image->getClientOriginalExtension();
            $this->uploadOne($image, $folder, 'public', $name);
            $image_Path = $filePath;
        }

        return $image_Path;
    }

    public
    function getSessionDuration($session_duration_from_request)
    {
        $session_duration = $session_duration_from_request;
        $round = $session_duration[1] / 10;
        $session_duration[1] = $round > 0.4 ? 5 : 0;
        $time = Carbon::parse('00:00:00');
        return $time->addMinute($session_duration);
    }
}
