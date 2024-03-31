<?php

namespace App\Http\Controllers;

use App\Models\BlockedPatient;
use App\Models\Patient;
use Illuminate\Http\Request;

class BlockedPatientController extends Controller
{
    public function index()
    {
        $data = Patient::whereIsActive(false)->get();

        return view('admin.patient.index')->with(['data'=>$data]);
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
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BlockedPatient  $blockedPatient
     * @return \Illuminate\Http\Response
     */
    public function show(BlockedPatient $blockedPatient)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BlockedPatient  $blockedPatient
     * @return \Illuminate\Http\Response
     */
    public function edit(BlockedPatient $blockedPatient)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BlockedPatient  $blockedPatient
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BlockedPatient $blockedPatient)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BlockedPatient  $blockedPatient
     * @return \Illuminate\Http\Response
     */
    public function destroy(BlockedPatient $blockedPatient)
    {
        //
    }
}
