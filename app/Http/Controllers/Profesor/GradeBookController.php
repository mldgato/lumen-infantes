<?php

namespace App\Http\Controllers\Profesor;

use App\Http\Controllers\Controller;

class GradeBookController extends Controller
{
    public function index()
    {
        return view('profesor.grade-books.index');
    }
}
