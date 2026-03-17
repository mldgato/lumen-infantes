<?php

namespace Database\Seeders;

use App\Models\MedicalRecord;
use App\Models\Student;
use App\Models\Image;
use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\StudentEnrollment;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Datos de estudiantes extraídos de los PDFs agrupados por carrera, grado y sección.
        $estudiantesPorGrupo = [
            8 => [
                ['first' => 'Alba', 'middle' => 'Ysel', 'surname' => 'Balán', 'second' => 'Mendez', 'dob' => '2004-01-11', 'cui' => '320-136-61', 'code' => 'D445WDI', 'gender' => 'Femenino'],
                ['first' => 'Kimberly', 'middle' => 'Janneth', 'surname' => 'Chamalé', 'second' => 'Ajcip', 'dob' => '2009-08-20', 'cui' => '2068328770101', 'code' => 'H851TYB', 'gender' => 'Femenino'],
                ['first' => 'Alejandra', 'middle' => 'Yanira', 'surname' => 'Galicio', 'second' => 'Vásquez', 'dob' => '2010-03-19', 'cui' => '2210719622102', 'code' => 'H990PWM', 'gender' => 'Femenino'],
                ['first' => 'Angela', 'middle' => 'Cristina', 'surname' => 'Girón', 'second' => 'Galvez', 'dob' => '1988-08-11', 'cui' => '1667976340101', 'code' => 'T924LJL', 'gender' => 'Femenino'],
                ['first' => 'Selvin', 'middle' => 'Daniel', 'surname' => 'Gonzalez', 'second' => 'López', 'dob' => '2010-02-26', 'cui' => '2107576041227', 'code' => '1434YBF', 'gender' => 'Masculino'],
                ['first' => 'Ashly', 'middle' => 'Melisa', 'surname' => 'Huertas', 'second' => 'Santizo', 'dob' => '2009-02-18', 'cui' => '2084767920101', 'code' => 'G462RGZ', 'gender' => 'Femenino'],
                ['first' => 'Yesenia', 'middle' => 'Carolina', 'surname' => 'Miranda', 'second' => 'Castañón', 'dob' => '2004-12-06', 'cui' => '3329405781221', 'code' => 'E784VTG', 'gender' => 'Femenino'],
                ['first' => 'José', 'middle' => 'Miguel', 'surname' => 'Miranda', 'second' => 'Toj', 'dob' => '2010-02-22', 'cui' => '2107665570101', 'code' => 'H094LBL', 'gender' => 'Masculino'],
                ['first' => 'Leidy', 'middle' => 'Saraí', 'surname' => 'Nufio', 'second' => 'Ramos', 'dob' => '1997-06-17', 'cui' => '3364453421904', 'code' => 'R717JTW', 'gender' => 'Femenino'],
                ['first' => 'Maria', 'middle' => 'Teresa', 'surname' => 'Pasán', 'second' => 'Rivera', 'dob' => '2009-06-18', 'cui' => '2445627920101', 'code' => 'H026MJX', 'gender' => 'Femenino'],
                ['first' => 'Katherin', 'middle' => 'Stefany', 'surname' => 'Ramirez', 'second' => 'Duarte', 'dob' => '2008-11-10', 'cui' => '2021050240101', 'code' => 'H140YFI', 'gender' => 'Femenino'],
                ['first' => 'Jefferson', 'middle' => 'Gustavo', 'surname' => 'Ramirez', 'second' => 'Mateo', 'dob' => '2009-01-01', 'cui' => '2042580490101', 'code' => 'F543RHV', 'gender' => 'Masculino'],
                ['first' => 'Génesis', 'middle' => 'Rosibel', 'surname' => 'Reyes', 'second' => 'Pérez', 'dob' => '2010-02-06', 'cui' => '2091726570108', 'code' => '1431RCB', 'gender' => 'Femenino'],
                ['first' => 'Jesus', 'middle' => 'Enrique', 'surname' => 'Trinidad', 'second' => 'Ixmatul', 'dob' => '1999-05-10', 'cui' => '2887857380101', 'code' => 'C214RYL', 'gender' => 'Masculino'],
                ['first' => 'Celia', 'middle' => 'Linda Yuridia', 'surname' => 'Villatoro', 'second' => 'Guamuch', 'dob' => '2008-01-11', 'cui' => '3022718050101', 'code' => 'F481QPL', 'gender' => 'Femenino'],
                ['first' => 'Estefani', 'middle' => 'Yasmin', 'surname' => 'Yojcom', 'second' => 'Mateo', 'dob' => '2005-04-24', 'cui' => '3415564832103', 'code' => 'E987GUX', 'gender' => 'Femenino'],
                ['first' => 'Mellani', 'middle' => 'Jasmin', 'surname' => 'Zamora', 'second' => 'Francisco', 'dob' => '2009-03-17', 'cui' => '2036896150101', 'code' => 'H972AQG', 'gender' => 'Femenino'],
            ],
            9 => [
                ['first' => 'Sofía', 'middle' => 'Modesta', 'surname' => 'Acetún', 'second' => 'Vasquez', 'dob' => '2007-12-20', 'cui' => '2000629131015', 'code' => 'G529PCS', 'gender' => 'Femenino'],
                ['first' => 'Hilda', 'middle' => 'Catarina', 'surname' => 'Ajanel', 'second' => 'Pirir', 'dob' => '2005-04-21', 'cui' => '3098974320805', 'code' => 'E234ANV', 'gender' => 'Femenino'],
                ['first' => 'Adyson', 'middle' => 'Betzabel', 'surname' => 'Cahuec', 'second' => 'Petzey', 'dob' => '2008-05-27', 'cui' => '2008145160101', 'code' => 'H732MQT', 'gender' => 'Femenino'],
                ['first' => 'Mayra', 'middle' => 'Vicelia', 'surname' => 'Chan', 'second' => 'Herrera', 'dob' => '1994-07-20', 'cui' => '2957958830808', 'code' => 'B606BJE', 'gender' => 'Femenino'],
                ['first' => 'Auner', 'middle' => 'Benjamin', 'surname' => 'Che', 'second' => 'Sacul', 'dob' => '2009-02-28', 'cui' => '2032358781420', 'code' => 'G843AJD', 'gender' => 'Masculino'],
                ['first' => 'Angel', 'middle' => 'Geovanny', 'surname' => 'Cosajay', 'second' => 'Villela', 'dob' => '2008-08-18', 'cui' => '3031954440108', 'code' => 'G682SNW', 'gender' => 'Masculino'],
                ['first' => 'Fredy', 'middle' => 'Steven', 'surname' => 'Cú', 'second' => 'Súchite', 'dob' => '2008-02-11', 'cui' => '2001458351508', 'code' => 'F892ACJ', 'gender' => 'Masculino'],
                ['first' => 'Carlos', 'middle' => 'Wilson Alfredo', 'surname' => 'Flores', 'second' => 'Chacon', 'dob' => '2001-02-13', 'cui' => '3031400540108', 'code' => 'C714BQW', 'gender' => 'Masculino'],
                ['first' => 'Jam\'ss', 'middle' => 'Estuardo Joel', 'surname' => 'González', 'second' => null, 'dob' => '1991-07-28', 'cui' => '2078051130101', 'code' => 'C708AVH', 'gender' => 'Masculino'],
                ['first' => 'Cindy', 'middle' => 'Samara Desiree', 'surname' => 'González', 'second' => 'Osorio', 'dob' => '2007-01-24', 'cui' => '3911895810101', 'code' => 'E360KMU', 'gender' => 'Femenino'],
                ['first' => 'Andy', 'middle' => 'Samuel', 'surname' => 'Huertas', 'second' => 'Santizo', 'dob' => '2009-02-26', 'cui' => '2035067070101', 'code' => 'G973MEQ', 'gender' => 'Masculino'],
                ['first' => 'Victoria', 'middle' => 'Saraí', 'surname' => 'Miron', 'second' => 'Osorio', 'dob' => '2008-09-07', 'cui' => '2014443640101', 'code' => 'G831YFL', 'gender' => 'Femenino'],
                ['first' => 'Lesly', 'middle' => 'Paola', 'surname' => 'Ramírez', 'second' => 'Maldonado', 'dob' => '2007-03-19', 'cui' => '3398628902101', 'code' => 'E628LKL', 'gender' => 'Femenino'],
                ['first' => 'Yeimi', 'middle' => 'Jimena', 'surname' => 'Ruiz', 'second' => 'De León', 'dob' => '2008-12-27', 'cui' => '2030113170101', 'code' => 'G670EJR', 'gender' => 'Femenino'],
                ['first' => 'Sindy', 'middle' => 'Aracely', 'surname' => 'Salic', 'second' => 'De León', 'dob' => '2005-09-09', 'cui' => '3323838061219', 'code' => 'E159ALH', 'gender' => 'Femenino'],
                ['first' => 'David', 'middle' => 'Emanuel', 'surname' => 'Solano', 'second' => 'Díaz', 'dob' => '1999-11-23', 'cui' => '3090625270609', 'code' => 'C220SVH', 'gender' => 'Masculino'],
                ['first' => 'Jacqueline', 'middle' => 'Nallely', 'surname' => 'Vasquez', 'second' => 'Chamalé', 'dob' => '2007-08-24', 'cui' => '2004658990101', 'code' => 'F772XFF', 'gender' => 'Femenino'],
            ],
            10 => [
                ['first' => 'Kimverly', 'middle' => 'Mishel', 'surname' => 'Ambrocio', 'second' => 'Maldonado', 'dob' => '2008-01-30', 'cui' => '3275557851020', 'code' => 'G294MZX', 'gender' => 'Femenino'],
                ['first' => 'Lilian', 'middle' => 'Elizabeth', 'surname' => 'Cajabon', 'second' => 'Ordoñez', 'dob' => '2000-12-04', 'cui' => '2838966120108', 'code' => 'D240FMP', 'gender' => 'Femenino'],
                ['first' => 'Isabel', 'middle' => null, 'surname' => 'Castañeda', 'second' => 'Rosales', 'dob' => '2007-06-16', 'cui' => '2902884871013', 'code' => 'E937ZAZ', 'gender' => 'Femenino'],
                ['first' => 'Luis', 'middle' => 'Emanuel', 'surname' => 'Escobar', 'second' => 'Raxon', 'dob' => '2006-12-26', 'cui' => '3923636310101', 'code' => 'E628ZWR', 'gender' => 'Masculino'],
                ['first' => 'Lesly', 'middle' => 'Zulema', 'surname' => 'Macario', 'second' => 'Chet', 'dob' => '2005-12-04', 'cui' => '3013984370101', 'code' => 'E131AYW', 'gender' => 'Femenino'],
                ['first' => 'Wilder', 'middle' => 'Aroldo', 'surname' => 'Miranda', 'second' => 'Xujur', 'dob' => '2005-11-18', 'cui' => '3031181720108', 'code' => 'C253DGV', 'gender' => 'Masculino'],
                ['first' => 'Lester', 'middle' => 'Oseas', 'surname' => 'Oxlaj', 'second' => 'González', 'dob' => '2008-08-17', 'cui' => '2305619770108', 'code' => 'G645HBI', 'gender' => 'Masculino'],
                ['first' => 'Jaqueline', 'middle' => 'Asucena', 'surname' => 'Piox', 'second' => 'Tecú', 'dob' => '2006-08-13', 'cui' => '3462013570101', 'code' => 'F189EBX', 'gender' => 'Femenino'],
                ['first' => 'Stefanni', 'middle' => 'Yamileth', 'surname' => 'Pérez', 'second' => 'Camajá', 'dob' => '2008-05-02', 'cui' => '2003541080101', 'code' => 'G530TQF', 'gender' => 'Femenino'],
                ['first' => 'Dennis', 'middle' => 'Juan Carlos', 'surname' => 'Quintana', 'second' => 'Us', 'dob' => '2006-09-10', 'cui' => '3270185181015', 'code' => 'F854ZTJ', 'gender' => 'Masculino'],
                ['first' => 'Angy', 'middle' => 'Gabriela', 'surname' => 'Ramírez', 'second' => 'Baltazar', 'dob' => '2008-05-14', 'cui' => '2006917400101', 'code' => 'F947BYU', 'gender' => 'Femenino'],
                ['first' => 'Grisel', 'middle' => 'Bertha Ruby', 'surname' => 'Sequen', 'second' => 'Vásquez', 'dob' => '2007-04-17', 'cui' => '3648876800101', 'code' => 'E580BLL', 'gender' => 'Femenino'],
                ['first' => 'Fatima', 'middle' => 'Adriana', 'surname' => 'Zacarias', 'second' => 'Candido', 'dob' => '2007-07-13', 'cui' => '3915634240101', 'code' => 'F384QCD', 'gender' => 'Femenino'],
            ],
            7 => [
                ['first' => 'Madelin', 'middle' => 'Dallana', 'surname' => 'Asún', 'second' => 'Sánchez', 'dob' => '2006-12-05', 'cui' => '3717512920101', 'code' => 'E593NTX', 'gender' => 'Femenino'],
                ['first' => 'Anthony', 'middle' => 'Ricardo', 'surname' => 'Carrillo', 'second' => 'Celada', 'dob' => '2006-01-04', 'cui' => '2924048240101', 'code' => 'D899CWZ', 'gender' => 'Masculino'],
                ['first' => 'Astryd', 'middle' => 'Sherlin', 'surname' => 'López', 'second' => 'Us', 'dob' => '2006-09-03', 'cui' => '3909643600108', 'code' => 'E055SCG', 'gender' => 'Femenino'],
                ['first' => 'Jaquelyn', 'middle' => 'Andrea', 'surname' => 'Osorio', 'second' => 'García', 'dob' => '2006-08-18', 'cui' => '3641023300101', 'code' => '1736QTM', 'gender' => 'Femenino'],
                ['first' => 'Dilan', 'middle' => 'Scary', 'surname' => 'Pérez', 'second' => 'Hernández', 'dob' => '2006-08-26', 'cui' => '3714567700101', 'code' => 'F999PZS', 'gender' => 'Masculino'],
                ['first' => 'Wendy', 'middle' => 'Lizeth', 'surname' => 'Ramirez', 'second' => 'Maldonado', 'dob' => '2005-01-03', 'cui' => '3402877052101', 'code' => 'D884JFW', 'gender' => 'Femenino'],
                ['first' => 'Gustavo', 'middle' => 'Aaron', 'surname' => 'Raxón', 'second' => 'Cotzojay', 'dob' => '2007-12-26', 'cui' => '2014995170101', 'code' => 'F791PAW', 'gender' => 'Masculino'],
                ['first' => 'Karen', 'middle' => 'Suceli', 'surname' => 'Rodríguez', 'second' => 'Segura', 'dob' => '1995-11-10', 'cui' => '2927879870101', 'code' => 'D049ZBE', 'gender' => 'Femenino'],
                ['first' => 'Ashley', 'middle' => 'Nicole', 'surname' => 'Rustrian', 'second' => 'Morales', 'dob' => '2008-05-05', 'cui' => '2006179820102', 'code' => 'F856PQD', 'gender' => 'Femenino'],
                ['first' => 'Josue', 'middle' => 'Emanuel', 'surname' => 'Sian', 'second' => null, 'dob' => '1994-09-09', 'cui' => '2516180800101', 'code' => 'C808WUU', 'gender' => 'Masculino'],
                ['first' => 'Eduardo', 'middle' => 'Antonio', 'surname' => 'Sintuj', 'second' => 'Jimenez', 'dob' => '2004-01-17', 'cui' => '4047040930101', 'code' => 'E461GML', 'gender' => 'Masculino'],
                ['first' => 'Luis', 'middle' => 'David', 'surname' => 'Sánchez', 'second' => 'Itzep', 'dob' => '2006-11-19', 'cui' => '3246541541006', 'code' => 'F834TJK', 'gender' => 'Masculino'],
                ['first' => 'Kateryn', 'middle' => 'Fabiola', 'surname' => 'Yanes', 'second' => 'Batres', 'dob' => '2009-05-20', 'cui' => '2043535560101', 'code' => '1837RPI', 'gender' => 'Femenino'],
            ],
            6 => [
                ['first' => 'Sara', 'middle' => 'Gabriela', 'surname' => 'Baltazar', 'second' => 'Aguilar', 'dob' => '2010-01-04', 'cui' => '2088251180101', 'code' => 'G058LMQ', 'gender' => 'Femenino'],
                ['first' => 'Esdras', 'middle' => 'Santiago', 'surname' => 'Chacaj', 'second' => 'Tarax', 'dob' => '2003-07-26', 'cui' => '3122120110806', 'code' => 'C263JAA', 'gender' => 'Masculino'],
                ['first' => 'Cristian', 'middle' => 'Gabriel', 'surname' => 'Chavac', 'second' => 'Soma', 'dob' => '2006-02-19', 'cui' => '3586556250101', 'code' => 'C490USM', 'gender' => 'Masculino'],
                ['first' => 'Stacy', 'middle' => 'Vanesa', 'surname' => 'Culajay', 'second' => 'Salazar', 'dob' => '2009-07-29', 'cui' => '2055565660101', 'code' => 'G739HUG', 'gender' => 'Femenino'],
                ['first' => 'Yanira', 'middle' => 'Guadalupe Mayte', 'surname' => 'Fuentes', 'second' => 'Fuentes', 'dob' => '2008-12-29', 'cui' => '2032994500101', 'code' => 'G362AGD', 'gender' => 'Femenino'],
                ['first' => 'Kevin', 'middle' => 'Emanuel', 'surname' => 'García', 'second' => 'Equite', 'dob' => '2005-08-30', 'cui' => '3032100280108', 'code' => 'C315XVV', 'gender' => 'Masculino'],
                ['first' => 'Erick', 'middle' => 'Otoniel', 'surname' => 'Humler', 'second' => 'Cac', 'dob' => '2008-01-20', 'cui' => '3359789241616', 'code' => 'H535FQB', 'gender' => 'Masculino'],
                ['first' => 'Josué', 'middle' => 'Daniel', 'surname' => 'Itzep', 'second' => null, 'dob' => '2009-02-04', 'cui' => '2821762410108', 'code' => 'H181QMJ', 'gender' => 'Masculino'],
                ['first' => 'Dulce', 'middle' => 'Maria', 'surname' => 'López', 'second' => null, 'dob' => '2007-10-19', 'cui' => '3023329440101', 'code' => 'G880YFP', 'gender' => 'Femenino'],
                ['first' => 'Josselyne', 'middle' => 'Adriana', 'surname' => 'Osorio', 'second' => 'García', 'dob' => '2000-04-30', 'cui' => '2999766880101', 'code' => 'C833CLR', 'gender' => 'Femenino'],
                ['first' => 'Gabriela', 'middle' => 'Abigail', 'surname' => 'Sopón', 'second' => 'Torres', 'dob' => '2008-11-15', 'cui' => '2033758820101', 'code' => 'G374YJP', 'gender' => 'Femenino'],
                ['first' => 'Oswin', 'middle' => 'Elias', 'surname' => 'Tohon', 'second' => 'Escalante', 'dob' => '2009-10-26', 'cui' => '2734939810101', 'code' => 'H632VCG', 'gender' => 'Masculino'],
                ['first' => 'Stiven', 'middle' => 'Josué', 'surname' => 'Tohon', 'second' => 'Escalante', 'dob' => '2008-11-02', 'cui' => '3461132450108', 'code' => 'H252BAX', 'gender' => 'Masculino'],
                ['first' => 'Anderson', 'middle' => 'Giovani', 'surname' => 'Tzul', 'second' => 'Cacatzun', 'dob' => '2006-04-18', 'cui' => '2903862760101', 'code' => 'D631EAA', 'gender' => 'Masculino'],
                ['first' => 'Eder', 'middle' => 'Alexander', 'surname' => 'Urias', 'second' => 'Galicia', 'dob' => '2008-11-17', 'cui' => '3467282900101', 'code' => 'H433HWW', 'gender' => 'Masculino'],
                ['first' => 'Angel', 'middle' => 'Elias', 'surname' => 'Zacarías', 'second' => 'Candido', 'dob' => '2009-06-10', 'cui' => '2048827610101', 'code' => '1639HGZ', 'gender' => 'Masculino'],
            ],
        ];

        $contadorGlobal = 1;

        foreach ($estudiantesPorGrupo as $classroomId => $estudiantes) {
            $this->command->info("Procesando Aula ID: {$classroomId} (" . count($estudiantes) . " alumnos)");

            foreach ($estudiantes as $data) {
                // 1. Crear el usuario aprovechando el factory para generar un password default y la lógica del modelo
                $user = User::factory()->create([
                    'first_name'      => $data['first'],
                    'middle_name'     => $data['middle'],
                    'surname'         => $data['surname'],
                    'second_surname'  => $data['second'],
                    'married_surname' => null, // Ninguno de los PDFs presentaba formato explícito de casada "de..."
                    'birthdate'       => $data['dob'],
                    'cui'             => $data['cui'],
                    'gender'          => $data['gender'],
                    'email'           => "student{$contadorGlobal}@lumen.net", // Correo institucional genérico
                ]);

                // 2. Crear el estudiante vinculado
                $student = Student::factory()->create([
                    'user_id'         => $user->id,
                    'personal_code'   => $data['code'],
                    'carne'           => $data['code'],
                    'is_own_guardian' => false,
                ]);

                // 3. Asignar el rol al usuario
                $user->assignRole('Estudiante');

                // 4. Crear imagen polimórfica utilizando el factory existente
                $user->image()->save(Image::factory()->make());

                // 5. Crear el registro médico vinculado al usuario dejando los demás campos vacíos
                MedicalRecord::create([
                    'user_id' => $user->id
                ]);

                StudentEnrollment::create([
                    'student_id'   => $student->id,
                    'classroom_id' => $classroomId,
                    'status'       => 'Activo',
                ]);

                $contadorGlobal++;
            }
        }

        $this->command->info("¡Carga masiva finalizada exitosamente! Se procesaron " . ($contadorGlobal - 1) . " alumnos reales.");
    }
}
