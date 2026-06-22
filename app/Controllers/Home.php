<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        if (logged_in()) {
            return redirect()->to('backend/dashboard');
        }
        return view('public/landing');
    }
}
