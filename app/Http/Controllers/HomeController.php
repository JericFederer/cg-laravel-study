<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    // * This function will render the home page
    public function home() {
        return view('home');
    }
}
