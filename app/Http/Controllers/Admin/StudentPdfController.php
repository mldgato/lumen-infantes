<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Helpers\PDF;
use Carbon\Carbon;

class StudentPdfController extends Controller
{
    public function generate(User $student)
    {
        if (!$student->hasRole('Estudiante')) {
            abort(403, 'El usuario seleccionado no es un estudiante.');
        }

        $student->load(['student.guardians', 'medicalRecord', 'image']);

        $pdf = new PDF('P', 'mm', [210, 279]);
        $pdf->AliasNbPages();
        $pdf->AddPage();

        // LOGO E INTERFAZ
        $logoPath = env('APP_INSTITUTION_LOGO_IMG', 'vendor/adminlte/dist/img/Escudo.png');
        $pdf->addImage($logoPath, 10, 8, 30);

        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(80);
        $nombreColegio = strip_tags(env('APP_INSTITUTION_NAME', 'Institución Educativa'));
        $pdf->CellUTF8(30, 5, 'Colegio ' . $nombreColegio, 0, 1, 'C');
        $pdf->Cell(80);
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->CellUTF8(30, 5, $pdf->dec('Fundado en 1781'), 0, 1, 'C'); // CORREGIDO AÑO Y TEXTO
        $pdf->Ln(2);
        $pdf->Cell(80);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->CellUTF8(30, 5, 'Ficha de Datos del Alumno', 0, 1, 'C');
        $pdf->Cell(80);
        $pdf->CellUTF8(30, 5, 'Ciclo ' . date('Y'), 0, 1, 'C');
        $pdf->Ln(10);

        // FOTO DE PERFIL
        $pdf->SetLineWidth(0.6);
        $pdf->Rect(165, 10, 35, 45);
        if ($student->image) {
            $fotoPath = storage_path('app/public/' . $student->image->url);
            $pdf->addSafeImage($fotoPath, 166, 11, 33, 43);
        } else {
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetXY(165, 30);
            $pdf->CellUTF8(35, 5, 'FOTO', 0, 0, 'C');
        }

        $pdf->SetY(60);

        // ==========================================
        // 1. DATOS DEL ALUMNO
        // ==========================================
        $pdf->SetLineWidth(0.2);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(200, 200, 200);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->CellUTF8(189, 5, 'DATOS DEL ALUMNO', 0, 1, 'C', true);
        $pdf->Ln(2);
        $pdf->SetFont('Arial', '', 9);

        $pdf->CellUTF8(93, 5, $pdf->dec($student->first_name . ' ' . $student->middle_name), "B", 0, 'C');
        $pdf->CellUTF8(3, 5, "", 0, 0, 'C');
        $pdf->CellUTF8(93, 5, $pdf->dec($student->surname . ' ' . $student->second_surname), "B", 1, 'C');
        $pdf->SetFont('Arial', '', 7);
        $pdf->CellUTF8(93, 4, "Nombres", 0, 0, 'C');
        $pdf->CellUTF8(3, 4, "", 0, 0, 'C');
        $pdf->CellUTF8(93, 4, "Apellidos", 0, 1, 'C');
        $pdf->Ln(2);

        $pdf->SetFont('Arial', '', 9);
        $fechaNac = $student->birthdate ? Carbon::parse($student->birthdate) : null;
        $pdf->CellUTF8(61, 5, $student->cui ?? 'N/A', "B", 0, 'C');
        $pdf->CellUTF8(3, 5, "", 0, 0, 'C');
        $pdf->CellUTF8(61, 5, ($fechaNac ? $fechaNac->format('d/m/Y') : 'N/A'), "B", 0, 'C');
        $pdf->CellUTF8(3, 5, "", 0, 0, 'C');
        $pdf->CellUTF8(61, 5, ($fechaNac ? $fechaNac->age : '0') . ' ' . $pdf->dec('años'), "B", 1, 'C');
        $pdf->SetFont('Arial', '', 7);
        $pdf->CellUTF8(61, 4, "CUI", 0, 0, 'C');
        $pdf->CellUTF8(3, 4, "", 0, 0, 'C');
        $pdf->CellUTF8(61, 4, "Fecha de Nacimiento", 0, 0, 'C');
        $pdf->CellUTF8(3, 4, "", 0, 0, 'C');
        $pdf->CellUTF8(61, 4, "Edad Actual", 0, 1, 'C');
        $pdf->Ln(2);

        $pdf->SetFont('Arial', '', 9);
        $pdf->CellUTF8(45, 5, $student->gender ?? 'N/A', "B", 0, 'C');
        $pdf->CellUTF8(3, 5, "", 0, 0, 'C');
        $pdf->CellUTF8(45, 5, $student->cellphone ?? 'N/A', "B", 0, 'C');
        $pdf->CellUTF8(3, 5, "", 0, 0, 'C');
        $pdf->CellUTF8(93, 5, $pdf->dec($student->address ?? 'N/A'), "B", 1, 'C');
        $pdf->SetFont('Arial', '', 7);
        $pdf->CellUTF8(45, 4, $pdf->dec('Género'), 0, 0, 'C');
        $pdf->CellUTF8(3, 4, "", 0, 0, 'C');
        $pdf->CellUTF8(45, 4, "Celular", 0, 0, 'C');
        $pdf->CellUTF8(3, 4, "", 0, 0, 'C');
        $pdf->CellUTF8(93, 4, $pdf->dec('Dirección Completa'), 0, 1, 'C');
        $pdf->Ln(10);

        // ==========================================
        // 2. DATOS MÉDICOS (HOJA 1)
        // ==========================================
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(200, 200, 200);
        $pdf->CellUTF8(189, 5, "DATOS MÉDICOS", "T", 1, 'C', true);
        $pdf->Ln(2);

        if ($student->medicalRecord) {
            $pdf->SetFont('Arial', '', 9);
            $med = $student->medicalRecord;
            $pdf->CellUTF8(61, 5, $med->blood_type ?? 'N/A', "B", 0, 'C');
            $pdf->CellUTF8(3, 5, "", 0, 0, 'C');
            $pdf->CellUTF8(61, 5, ($med->weight ? $med->weight . ' lb' : 'N/A'), "B", 0, 'C');
            $pdf->CellUTF8(3, 5, "", 0, 0, 'C');
            $pdf->CellUTF8(61, 5, ($med->height ? $med->height . ' m' : 'N/A'), "B", 1, 'C');
            $pdf->SetFont('Arial', '', 7);
            $pdf->CellUTF8(61, 4, "Tipo de Sangre", 0, 0, 'C');
            $pdf->CellUTF8(3, 4, "", 0, 0, 'C');
            $pdf->CellUTF8(61, 4, "Peso", 0, 0, 'C');
            $pdf->CellUTF8(3, 4, "", 0, 0, 'C');
            $pdf->CellUTF8(61, 4, "Altura", 0, 1, 'C');
            $pdf->Ln(6);

            $this->printMedicalCondition($pdf, $pdf->dec("¿Toma algún medicamento?"), $med->takes_medication, $med->medication_description);
            $this->printMedicalCondition($pdf, $pdf->dec("¿Padece alguna enfermedad?"), $med->has_disease, $med->disease_description);
            $this->printMedicalCondition($pdf, $pdf->dec("¿Padece alguna alergia?"), $med->has_allergies, $med->allergies_description);
            $this->printMedicalCondition($pdf, $pdf->dec("¿Intervención quirúrgica?"), $med->had_surgery, $med->surgery_description);
        }

        // ==========================================
        // 3. GUARDIANES (HOJA 2 EN ADELANTE)
        // ==========================================
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(200, 200, 200);
        $pdf->CellUTF8(189, 5, "DATOS DE LOS PADRES O ENCARGADOS", "T", 1, 'C', true);
        $pdf->Ln(5);

        if ($student->student && $student->student->guardians->count() > 0) {
            $prioridad = ['Papá', 'Mamá', 'Encargado'];
            $guardians = $student->student->guardians->sortBy(fn($g) => array_search($g->pivot->relationship_type, $prioridad))->values();

            foreach ($guardians as $index => $guardian) {
                if ($index == 2) $pdf->AddPage(); // Salto para el tercero

                $pdf->SetFont('Arial', 'B', 9);
                $pdf->SetFillColor(235, 235, 235);
                $pdf->CellUTF8(189, 5, "DATOS DE: " . mb_strtoupper($guardian->pivot->relationship_type), 0, 1, 'L', true);
                $pdf->Ln(2);
                $pdf->SetFont('Arial', '', 9);

                $pdf->CellUTF8(93, 5, $pdf->dec($guardian->first_name), "B", 0, 'C');
                $pdf->CellUTF8(3, 5, "", 0, 0, 'C');
                $pdf->CellUTF8(93, 5, $pdf->dec($guardian->last_name), "B", 1, 'C');
                $pdf->SetFont('Arial', '', 7);
                $pdf->CellUTF8(93, 4, "Nombres", 0, 0, 'C');
                $pdf->CellUTF8(3, 4, "", 0, 0, 'C');
                $pdf->CellUTF8(93, 4, "Apellidos", 0, 1, 'C');
                $pdf->Ln(2);

                $pdf->SetFont('Arial', '', 9);
                $pdf->CellUTF8(61, 5, $pdf->dec($guardian->birthplace ?? 'N/A'), "B", 0, 'C');
                $pdf->CellUTF8(3, 5, "", 0, 0, 'C');
                $pdf->CellUTF8(61, 5, ($guardian->birthdate ? $guardian->birthdate->format('d/m/Y') : 'N/A'), "B", 0, 'C');
                $pdf->CellUTF8(3, 5, "", 0, 0, 'C');
                $pdf->CellUTF8(61, 5, $pdf->dec($guardian->nationality ?? 'N/A'), "B", 1, 'C');
                $pdf->SetFont('Arial', '', 7);
                $pdf->CellUTF8(61, 4, "Lugar de Nacimiento", 0, 0, 'C');
                $pdf->CellUTF8(3, 4, "", 0, 0, 'C');
                $pdf->CellUTF8(61, 4, "Fecha de Nacimiento", 0, 0, 'C');
                $pdf->CellUTF8(3, 4, "", 0, 0, 'C');
                $pdf->CellUTF8(61, 4, "Nacionalidad", 0, 1, 'C');
                $pdf->Ln(2);

                $pdf->SetFont('Arial', '', 9);
                $pdf->CellUTF8(61, 5, $guardian->cui ?? 'N/A', "B", 0, 'C');
                $pdf->CellUTF8(3, 5, "", 0, 0, 'C');
                $pdf->CellUTF8(61, 5, $pdf->dec($guardian->cui_extended_in ?? 'N/A'), "B", 0, 'C');
                $pdf->CellUTF8(3, 5, "", 0, 0, 'C');
                $pdf->CellUTF8(61, 5, $pdf->dec($guardian->profession ?? 'N/A'), "B", 1, 'C');
                $pdf->SetFont('Arial', '', 7);
                $pdf->CellUTF8(61, 4, "DPI / CUI", 0, 0, 'C');
                $pdf->CellUTF8(3, 4, "", 0, 0, 'C');
                $pdf->CellUTF8(61, 4, "Extendido en", 0, 0, 'C');
                $pdf->CellUTF8(3, 4, "", 0, 0, 'C');
                $pdf->CellUTF8(61, 4, $pdf->dec("Profesión u Oficio"), 0, 1, 'C'); // CORREGIDA TILDE
                $pdf->Ln(2);

                $pdf->SetFont('Arial', '', 9);
                $pdf->CellUTF8(189, 5, $pdf->dec($guardian->residence_address ?? 'N/A'), "B", 1, 'C');
                $pdf->SetFont('Arial', '', 7);
                $pdf->CellUTF8(189, 4, $pdf->dec("Dirección de Residencia"), 0, 1, 'C');
                $pdf->Ln(2);

                $pdf->SetFont('Arial', '', 9);
                $pdf->CellUTF8(93, 5, $guardian->phone ?? 'N/A', "B", 0, 'C');
                $pdf->CellUTF8(3, 5, "", 0, 0, 'C');
                $pdf->CellUTF8(93, 5, $guardian->email ?? 'N/A', "B", 1, 'C');
                $pdf->SetFont('Arial', '', 7);
                $pdf->CellUTF8(93, 4, $pdf->dec("Teléfono / Celular"), 0, 0, 'C');
                $pdf->CellUTF8(3, 4, "", 0, 0, 'C');
                $pdf->CellUTF8(93, 4, "Correo Electronico", 0, 1, 'C');
                $pdf->Ln(4);

                // Info Laboral
                $pdf->SetFillColor(245, 245, 245);
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->CellUTF8(189, 5, "INFORMACION LABORAL", 1, 1, 'L', true);
                $pdf->SetFont('Arial', '', 8);
                $pdf->CellUTF8(94.5, 5, "Empresa: " . $pdf->dec($guardian->company_name ?? 'N/A'), 1, 0, 'L');
                $pdf->CellUTF8(94.5, 5, $pdf->dec("Tel. Empresa: ") . ($guardian->company_phone ?? 'N/A'), 1, 1, 'L');
                $pdf->CellUTF8(189, 5, $pdf->dec("Dirección Empresa: ") . $pdf->dec($guardian->company_address ?? 'N/A'), 1, 1, 'L');
                $pdf->Ln(6);
            }
        }

        // ==========================================
        // 4. FIRMAS
        // ==========================================
        if ($pdf->GetY() > 210) $pdf->AddPage();
        $pdf->Ln(5);
        $pdf->SetFont('Arial', '', 10);
        $manifiesto = "Manifiesto que todos los datos proporcionados son verídicos y me comprometo a dar informe al Colegio cuando exista una modificación a los mismos; así mismo ACEPTO las cuotas y compromisos expresados en el manual de convivencia.";
        $pdf->MultiCellUTF8(189, 5, $pdf->dec($manifiesto), 0, 'J');

        $pdf->Ln(20);
        $pdf->CellUTF8(60, 5, "", "B", 0, 'C');
        $pdf->CellUTF8(4.5, 5, "", 0, 0);
        $pdf->CellUTF8(60, 5, "", "B", 0, 'C');
        $pdf->CellUTF8(4.5, 5, "", 0, 0);
        $pdf->CellUTF8(60, 5, "", "B", 1, 'C');
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->CellUTF8(60, 5, "Firma del Padre", 0, 0, 'C');
        $pdf->CellUTF8(4.5, 5, "", 0, 0);
        $pdf->CellUTF8(60, 5, "Firma de la Madre", 0, 0, 'C');
        $pdf->CellUTF8(4.5, 5, "", 0, 0);
        $pdf->CellUTF8(60, 5, "Firma del Encargado", 0, 1, 'C');

        return response($pdf->Output('S'), 200)->header('Content-Type', 'application/pdf');
    }

    private function printMedicalCondition($pdf, $pregunta, $condicionBool, $descripcion)
    {
        $pdf->SetFont('Arial', '', 9);
        $pdf->CellUTF8(50, 5, $pregunta, 0, 0, 'L');

        // Ajuste de cuadros para que la X no se encime
        $pdf->CellUTF8(5, 5, "Si", 0, 0, 'L');
        $pdf->CellUTF8(5, 4, ($condicionBool ? "X" : ""), 1, 0, 'C');
        $pdf->CellUTF8(2, 5, "", 0, 0);
        $pdf->CellUTF8(5, 5, "No", 0, 0, 'L');
        $pdf->CellUTF8(5, 4, (!$condicionBool ? "X" : ""), 1, 0, 'C');

        $pdf->CellUTF8(15, 5, " Detalles: ", 0, 0, 'L');
        $pdf->CellUTF8(102, 5, $condicionBool ? $pdf->dec($descripcion ?? 'N/A') : "N/A", "B", 1, 'L');
        $pdf->Ln(4);
    }
}
