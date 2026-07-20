<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use App\Models\User;
use Illuminate\Http\Request;

class HospitalController extends Controller
{
    //
    public function getHospital()
    {
        $hospital = Hospital::with(['nurse_leader', 'boss'])->first();
        return response()->json([
            'hospital' => $hospital,
            'users' => User::all()
        ]);
    }

    public function updateHospital(Request $request)
    {
        $hospital = Hospital::first();
        $hospital->update($request->all());

        $hospital = Hospital::with(['nurse_leader', 'boss'])->first();

        return response()->json(['status' => 200, 'message' => '更新成功', 'hospital' => $hospital]);
    }
}
