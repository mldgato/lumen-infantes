<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SabanaUnidadExport;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function sabanaUnidad()
    {
        return view('admin.reports.sabana-unidad.index');
    }

    public function exportSabanaUnidad(Request $request)
    {
        $request->validate([
            'year'    => 'required',
            'level'   => 'required|exists:levels,id',
            'grade'   => 'required|exists:grades,id',
            'section' => 'required|exists:sections,id',
            'unit'    => 'required|integer|min:1',
        ]);

        $classroom = Classroom::where('year', $request->year)
            ->where('level_id', $request->level)
            ->where('grade_id', $request->grade)
            ->where('section_id', $request->section)
            ->firstOrFail();

        $filename = 'Sabana_U' . $request->unit . '_' . date('dmY_His') . '.xlsx';

        return Excel::download(
            new SabanaUnidadExport($classroom->id, (int) $request->unit),
            $filename
        );
    }
}
