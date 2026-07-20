<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //
    function index(){
        return response()->json([
            'status' => 200,
            'users' => User::all(),
            'roles' => Role::all()
        ]);
    }
}
