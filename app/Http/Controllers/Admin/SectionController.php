<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class SectionController extends Controller
{
    public function index()
    {
        return view('admin.sections.index');
    }
}
