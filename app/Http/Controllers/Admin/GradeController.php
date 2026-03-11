<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class GradeController extends Controller
{
    public function index()
    {
        return view('admin.grades.index');
    }
}
