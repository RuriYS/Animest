<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ControllerAbstract;

class MainController extends ControllerAbstract {
    public function index() {
        return view('home');
    }
}
