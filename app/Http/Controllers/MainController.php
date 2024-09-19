<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class MainController extends Controller
{
    public function index()
    {
        return view('home');
    }

    public function test()
    {
        return response('Hi', 200);
    }
}
