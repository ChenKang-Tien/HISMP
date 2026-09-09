<?php

namespace App\Http\Controllers;

use App\Models\RoutesOfAdministration;
use Illuminate\Http\Request;

class RouteOfAdministrationController extends Controller
{
    public function index()
    {
        return response()->json(RoutesOfAdministration::where('deleted', 0)->get(['id', 'name']));
    }
}
