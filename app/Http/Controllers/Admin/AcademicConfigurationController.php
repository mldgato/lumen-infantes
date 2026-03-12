<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AcademicConfigurationController extends Controller
{
    public function index()
    {
        return view('admin.academic-configurations.index');
    }
}
