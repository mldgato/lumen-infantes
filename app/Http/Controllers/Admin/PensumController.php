<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class PensumController extends Controller
{
    public function index()
    {
        return view('admin.pensums.index');
    }
}
