<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ActivityTypeController extends Controller
{
    public function index()
    {
        return view('admin.activity-types.index');
    }
}
