<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class UserController extends Controller
{
    public function Dashboard()
    {

        if (Auth::check() && Auth::user()->type == "user") { //Check if user is login or not and if type is user
            return view('dashboard');
        } else if (Auth::check() && Auth::user()->type == "admin") { //Check if user is login or not and if type is user
            return view('admin.dashboard');
        } else {
            // return redirect('/') ;
            return redirect()->route('login'); //If users is not loged in
        }
    }
}
