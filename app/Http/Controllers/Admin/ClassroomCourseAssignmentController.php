<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ClassroomCourseAssignmentController extends Controller
{
    public function index()
    {
        return view('admin.classroom-course-assignments.index');
    }
}
