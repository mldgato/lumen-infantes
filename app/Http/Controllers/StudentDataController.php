<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class StudentDataController extends Controller
{
    public function verifyToken(string $token): View
    {
        $data = Cache::get("student_update_{$token}");

        if (! $data) {
            return view('student-data.expired');
        }

        return view('student-data.form', [
            'token' => $token,
            'studentId' => $data['student_id'],
            'emailNuevo' => $data['email_nuevo'],
        ]);
    }
}
