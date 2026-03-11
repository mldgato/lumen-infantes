<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ClassroomController extends Controller
{
    public function index()
    {
        return view('admin.classrooms.index');
    }
}
