<?php

namespace App\Controllers\Backend;

use App\Controllers\BaseController;

class Help extends BaseController
{
    public function index()
    {
        $data = [
            'pageTitle' => 'Panduan & Bantuan',
            'user'      => user(),
        ];

        return view('backend/help/index', $data);
    }
}
