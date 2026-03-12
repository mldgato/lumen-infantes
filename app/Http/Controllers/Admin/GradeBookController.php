<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class GradeBookController extends Controller
{
    public function index()
    {
        return view('admin.grade-books.index');
    }
}
