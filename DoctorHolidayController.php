<?php

namespace App\Http\Controllers;

use App\Models\DoctorHoliday;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorHolidayController extends Controller
{

    public function index()
    {
        $holidays = DoctorHoliday::whereDoctorId(Auth::user()->id)->get();
        return view('website.Doctor.holiday')->with(['holidays' => $holidays]);
    }


    public function create()
    {
        $user = Auth::user();

        $date = Carbon::now()->format('y-m-d');
        $start_time = date('h:i a', strtotime($user->doctor->clinic->open_time));
        $end_time = date('h:i a', strtotime($user->doctor->clinic->close_time));
        return view('website.Doctor.addHoliday')->with([
            'date' => $date,
            'start_time' => $start_time,
            'end_time' => $end_time
        ]);

    }


    public function store(Request $request)
    {

        $request->validate([
            'date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:h:i a'],
            'end_time' => ['required', 'date_format:h:i a', 'after:start_time'],
        ]);


        $date = Carbon::parse($request->date)->format('Y-m-d');
        $start_time = date('H:i', strtotime($request->start_time));
        $end_time = date('H:i', strtotime($request->end_time));
        $doctor_id = Auth::user()->id;

        $holiday = new DoctorHoliday();
        $holiday->doctor_id = $doctor_id;
        $holiday->start_time = $start_time;
        $holiday->end_time = $end_time;
        $holiday->date = $date;
        $holiday->save();
        return redirect()->route('holidays.index');
    }


    public function edit(DoctorHoliday $doctorHoliday)
    {
        //
    }


    public function update(Request $request, DoctorHoliday $doctorHoliday)
    {
        //
    }


    public function destroy($id)
    {
        $holiday = DoctorHoliday::whereId($id)->first();

        if (!$holiday) abort(404);
        else $holiday->delete();

        return redirect()->route('holidays.index');
    }
}
