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
            1 => [
                ['first' => 'Abraham', 'middle' => 'Isaí', 'surname' => 'Alvizures', 'second' => 'Huit', 'dob' => '2011-04-16', 'cui' => '2257792690108', 'code' => 'I836SYU', 'gender' => 'Masculino'],
                ['first' => 'Briscelda', 'middle' => 'Alejandra', 'surname' => 'Asencio', 'second' => 'Hernández', 'dob' => '2011-04-02', 'cui' => '2236718350108', 'code' => 'I533KUT', 'gender' => 'Femenino'],
                ['first' => 'Eymi', 'middle' => 'Amarilis', 'surname' => 'Asencio', 'second' => 'Hernández', 'dob' => '2013-03-10', 'cui' => '2687060420920', 'code' => 'K401HFI', 'gender' => 'Femenino'],
                ['first' => 'Cesar', 'middle' => 'Eduardo', 'surname' => 'Batz', 'second' => 'Patal', 'dob' => '2011-02-17', 'cui' => '2778925380101', 'code' => 'I230UCE', 'gender' => 'Masculino'],
                ['first' => 'Melani', 'middle' => 'Jasmin', 'surname' => 'García', 'second' => 'Asún', 'dob' => '2011-05-31', 'cui' => '2394060280101', 'code' => 'H493URR', 'gender' => 'Femenino'],
                ['first' => 'Jimmy', 'middle' => 'Aldair', 'surname' => 'Hernández', 'second' => 'Hernández', 'dob' => '2012-06-27', 'cui' => '2493365880101', 'code' => 'K900IQI', 'gender' => 'Masculino'],
                ['first' => 'Emili', 'middle' => 'Susana', 'surname' => 'Huertas', 'second' => 'Santizo', 'dob' => '2012-12-12', 'cui' => '3646442140108', 'code' => 'I038IBD', 'gender' => 'Femenino'],
                ['first' => 'Aod', 'middle' => 'Yisrael', 'surname' => 'Ibarra', 'second' => 'Osorio', 'dob' => '2012-08-20', 'cui' => '2505360640101', 'code' => 'K800CBH', 'gender' => 'Masculino'],
                ['first' => 'Evan', 'middle' => 'Marcoly', 'surname' => 'Juárez', 'second' => 'Tzoc', 'dob' => '2011-03-03', 'cui' => '2586753300101', 'code' => 'I137XKI', 'gender' => 'Masculino'],
                ['first' => 'Dilan', 'middle' => 'Fernando', 'surname' => 'López', 'second' => 'Patzán', 'dob' => '2011-05-11', 'cui' => '2346580610101', 'code' => 'J137ENH', 'gender' => 'Masculino'],
                ['first' => 'Fernando', 'middle' => 'Jose', 'surname' => 'Maldonado', 'second' => 'Alonzo', 'dob' => '2011-10-11', 'cui' => '2314482280101', 'code' => 'J373IRP', 'gender' => 'Masculino'],
                ['first' => 'Pablo', 'middle' => 'Enrique', 'surname' => 'Monroy', 'second' => 'Chávez', 'dob' => '2012-06-14', 'cui' => '2434001280506', 'code' => 'L003TYX', 'gender' => 'Masculino'],
                ['first' => 'Cristian', 'middle' => 'Fernando', 'surname' => 'Montecinos', 'second' => 'Sebastián', 'dob' => '2011-03-24', 'cui' => '2266271010101', 'code' => 'K601HXJ', 'gender' => 'Masculino'],
                ['first' => 'Jennifer', 'middle' => 'Gabriela', 'surname' => 'Montecinos', 'second' => 'Sebastián', 'dob' => '2013-01-27', 'cui' => '2685726821417', 'code' => 'M408VDI', 'gender' => 'Femenino'],
                ['first' => 'Ruth', 'middle' => 'Yanabel', 'surname' => 'Méndez', 'second' => 'Dealtán', 'dob' => '2013-04-01', 'cui' => '2712925040101', 'code' => 'L803CAN', 'gender' => 'Femenino'],
                ['first' => 'Edgar', 'middle' => 'Osbeli', 'surname' => 'Méndez', 'second' => 'Mendoza', 'dob' => '2011-07-05', 'cui' => '2547490890101', 'code' => 'J047TNE', 'gender' => 'Masculino'],
                ['first' => 'Germaryori', 'middle' => 'Betzabe', 'surname' => 'Pirir', 'second' => 'Miranda', 'dob' => '2012-12-18', 'cui' => '2646343570101', 'code' => 'L903SKQ', 'gender' => 'Femenino'],
                ['first' => 'Katy', 'middle' => 'Lourdes', 'surname' => 'Pérez', 'second' => 'Hernández', 'dob' => '2012-01-19', 'cui' => '2350076090101', 'code' => 'L303DIE', 'gender' => 'Femenino'],
                ['first' => 'Cristopher', 'middle' => 'Miguel', 'surname' => 'Ramírez', 'second' => 'Maldonado', 'dob' => '2012-06-02', 'cui' => '2429297882101', 'code' => 'L703HSX', 'gender' => 'Masculino'],
                ['first' => 'Maura', 'middle' => 'Patricia', 'surname' => 'Ratzán', 'second' => 'Och', 'dob' => '2011-05-23', 'cui' => '2275724161013', 'code' => 'K102LNK', 'gender' => 'Femenino'],
                ['first' => 'Angíe', 'middle' => 'Elizabeth', 'surname' => 'Santiago', 'second' => 'Cacao', 'dob' => '2011-04-17', 'cui' => '2235416990101', 'code' => 'J529FIX', 'gender' => 'Femenino'],
                ['first' => 'Kevin', 'middle' => 'Brayan Antony', 'surname' => 'Sequén', 'second' => 'Quib', 'dob' => '2010-11-01', 'cui' => '2163487811601', 'code' => 'I431TAY', 'gender' => 'Masculino'],
                ['first' => 'Sharon', 'middle' => 'Pamela Berenice', 'surname' => 'Túm', 'second' => null, 'dob' => '2012-08-06', 'cui' => '2655949010101', 'code' => 'K600YCA', 'gender' => 'Femenino'],
                ['first' => 'Alexís', 'middle' => 'Estuardo', 'surname' => 'Valseca', 'second' => 'Chocooj', 'dob' => '2012-01-11', 'cui' => '2341689511601', 'code' => 'L003KUF', 'gender' => 'Masculino'],
            ],
            2 => [
                ['first' => 'Miseidy', 'middle' => 'Daniela', 'surname' => 'Ajualip', 'second' => 'Velásquez', 'dob' => '2010-07-09', 'cui' => '2138708191015', 'code' => 'H581VPM', 'gender' => 'Femenino'],
                ['first' => 'Flor', 'middle' => 'Esmeralda Rene', 'surname' => 'Barrios', 'second' => 'Ordoñez', 'dob' => '2009-10-28', 'cui' => '2076691470101', 'code' => 'H154RCJ', 'gender' => 'Femenino'],
                ['first' => 'Eddyn', 'middle' => 'Alan Estuardo', 'surname' => 'Caal', 'second' => 'Ja', 'dob' => '2009-02-28', 'cui' => '2042888781609', 'code' => 'G377STJ', 'gender' => 'Masculino'],
                ['first' => 'Josue', 'middle' => 'Alexander', 'surname' => 'Chiguichon', 'second' => 'Ruch', 'dob' => '2010-08-04', 'cui' => '3567042890108', 'code' => 'J772GLH', 'gender' => 'Masculino'],
                ['first' => 'Alma', 'middle' => 'Carina', 'surname' => 'Galicio', 'second' => 'Vásquez', 'dob' => '2000-08-25', 'cui' => '3410649392102', 'code' => 'C516UIB', 'gender' => 'Femenino'],
                ['first' => 'Andersón', 'middle' => 'Gabriel', 'surname' => 'García', 'second' => 'Ambrocio', 'dob' => '2010-08-12', 'cui' => '2144179050101', 'code' => 'I037WKS', 'gender' => 'Masculino'],
                ['first' => 'Byron', 'middle' => 'Estuardo', 'surname' => 'Gatica', 'second' => 'Parada', 'dob' => '2009-09-28', 'cui' => '2445595890101', 'code' => 'E232RLS', 'gender' => 'Masculino'],
                ['first' => 'Eddy', 'middle' => 'Josué', 'surname' => 'Gutierrez', 'second' => 'Flores', 'dob' => '2009-09-22', 'cui' => '2953760900108', 'code' => 'H781WEE', 'gender' => 'Masculino'],
                ['first' => 'Justin', 'middle' => 'Rodrigo', 'surname' => 'Jiménez', 'second' => 'González', 'dob' => '2009-12-03', 'cui' => '2093986040101', 'code' => 'I134LJA', 'gender' => 'Masculino'],
                ['first' => 'Maynor', 'middle' => 'Alexander', 'surname' => 'Larios', 'second' => 'Lastor', 'dob' => '2009-11-25', 'cui' => '2077485001015', 'code' => 'H740RLA', 'gender' => 'Masculino'],
                ['first' => 'Mario', 'middle' => 'Francisco', 'surname' => 'López', 'second' => 'Mas', 'dob' => '2008-11-27', 'cui' => '2012119631005', 'code' => 'G581PUK', 'gender' => 'Masculino'],
                ['first' => 'Emanuel', 'middle' => 'Isaac', 'surname' => 'López', 'second' => 'Ramos', 'dob' => '2008-02-21', 'cui' => '2526012460108', 'code' => 'G469LKK', 'gender' => 'Masculino'],
                ['first' => 'Darlyn', 'middle' => 'Aracely', 'surname' => 'Mejía', 'second' => 'Coronado', 'dob' => '2010-12-30', 'cui' => '2183552380101', 'code' => 'J146REB', 'gender' => 'Femenino'],
                ['first' => 'Axel', 'middle' => 'Daniel', 'surname' => 'Pacheco', 'second' => 'Moreno', 'dob' => '2011-03-03', 'cui' => '2236604690101', 'code' => 'I832RUZ', 'gender' => 'Masculino'],
                ['first' => 'Doroty', 'middle' => 'Angelita', 'surname' => 'Ramos', 'second' => 'Cruz', 'dob' => '2009-08-06', 'cui' => '2060018430101', 'code' => 'G841XQV', 'gender' => 'Femenino'],
                ['first' => 'Estefany', 'middle' => 'Tatiana', 'surname' => 'Ramírez', 'second' => 'Monterroso', 'dob' => '2010-03-04', 'cui' => '2289731040108', 'code' => 'H650EAR', 'gender' => 'Femenino'],
                ['first' => 'Jara', 'middle' => 'De Jesus', 'surname' => 'Reyes', 'second' => 'Patzán', 'dob' => '2009-05-30', 'cui' => '3451510700101', 'code' => 'I241NJN', 'gender' => 'Femenino'],
                ['first' => 'Alma', 'middle' => 'Dallana', 'surname' => 'Sequen', 'second' => 'Reyes', 'dob' => '2009-12-02', 'cui' => '2099924330108', 'code' => 'I839MME', 'gender' => 'Femenino'],
                ['first' => 'Denisse', 'middle' => 'Betzabe', 'surname' => 'Soto', 'second' => 'García', 'dob' => '2010-04-10', 'cui' => '2102239520108', 'code' => 'H363SYL', 'gender' => 'Femenino'],
                ['first' => 'Alan', 'middle' => 'Javier', 'surname' => 'Zapeta', 'second' => 'Altán', 'dob' => '2011-01-10', 'cui' => '2194079400101', 'code' => 'H754BNI', 'gender' => 'Masculino'],
            ],
            3 => [
                ['first' => 'Jenífer', 'middle' => 'Guadalupe', 'surname' => 'Aguilar', 'second' => 'Cardoza', 'dob' => '2009-07-21', 'cui' => '2052812220101', 'code' => 'H148TUD', 'gender' => 'Femenino'],
                ['first' => 'Alison', 'middle' => 'Mariana', 'surname' => 'Ajcip', 'second' => 'Olivares', 'dob' => '2010-11-20', 'cui' => '2162638590101', 'code' => 'J447UJQ', 'gender' => 'Femenino'],
                ['first' => 'Isabel', 'middle' => null, 'surname' => 'Ajeataz', 'second' => 'Rosales', 'dob' => '2011-09-28', 'cui' => '2302867551013', 'code' => 'K001KMG', 'gender' => 'Femenino'],
                ['first' => 'Fátima', 'middle' => 'Sofía', 'surname' => 'Albizurez', 'second' => 'Hernández', 'dob' => '2009-04-22', 'cui' => '2041357530108', 'code' => 'G857UAQ', 'gender' => 'Femenino'],
                ['first' => 'Kristel', 'middle' => 'Yuseily', 'surname' => 'Asún', 'second' => 'Morales', 'dob' => '2011-09-29', 'cui' => '3441991180101', 'code' => 'J865KTA', 'gender' => 'Femenino'],
                ['first' => 'Brayan', 'middle' => 'Estuardo', 'surname' => 'Asún', 'second' => 'Sánchez', 'dob' => '2011-03-09', 'cui' => '2246504830101', 'code' => 'I639UIT', 'gender' => 'Masculino'],
                ['first' => 'Tomasa', 'middle' => 'Nohemí', 'surname' => 'Castañeda', 'second' => 'Rosales', 'dob' => '2009-04-06', 'cui' => '2038520011013', 'code' => 'E368NEC', 'gender' => 'Femenino'],
                ['first' => 'Marlon', 'middle' => 'Adán', 'surname' => 'Chub', 'second' => 'Chub', 'dob' => '2006-06-26', 'cui' => '3292620841709', 'code' => 'E385NZN', 'gender' => 'Masculino'],
                ['first' => 'Alexander', 'middle' => 'Augusto Moisés', 'surname' => 'Coxaj', 'second' => 'García', 'dob' => '2011-04-29', 'cui' => '2192289141001', 'code' => 'I037ADI', 'gender' => 'Masculino'],
                ['first' => 'Anderson', 'middle' => 'Adiel', 'surname' => 'Guerra', 'second' => 'Ramirez', 'dob' => '2009-03-29', 'cui' => '2285628800101', 'code' => 'H261YHY', 'gender' => 'Masculino'],
                ['first' => 'Kimberly', 'middle' => 'Marián', 'surname' => 'Juarez', 'second' => 'Itzep', 'dob' => '2000-02-19', 'cui' => '3031226680108', 'code' => 'C222LDZ', 'gender' => 'Femenino'],
                ['first' => 'Gerardo', 'middle' => 'Javier', 'surname' => 'Juárez', 'second' => 'Del Cid', 'dob' => '2011-03-14', 'cui' => '2236606040101', 'code' => 'H932RSC', 'gender' => 'Masculino'],
                ['first' => 'Melani', 'middle' => 'Gabriela', 'surname' => 'Lejá', 'second' => 'Cortez', 'dob' => '2010-03-14', 'cui' => '3448631580101', 'code' => 'H995EYR', 'gender' => 'Femenino'],
                ['first' => 'Dennis', 'middle' => 'Steveen', 'surname' => 'López', 'second' => 'García', 'dob' => '2009-11-18', 'cui' => '2252710280101', 'code' => 'E872CDG', 'gender' => 'Masculino'],
                ['first' => 'Wilson', 'middle' => 'Estuardo', 'surname' => 'López', 'second' => 'Rafael', 'dob' => '2008-03-02', 'cui' => '2008686750101', 'code' => 'H188KZC', 'gender' => 'Masculino'],
                ['first' => 'Karla', 'middle' => 'Saraí', 'surname' => 'López', 'second' => 'Ramos', 'dob' => '2009-08-06', 'cui' => '2062071660301', 'code' => 'H750XNC', 'gender' => 'Femenino'],
                ['first' => 'Crista', 'middle' => 'Lucero', 'surname' => 'Martinez', 'second' => null, 'dob' => '2012-06-04', 'cui' => '2433850990108', 'code' => 'J050MWL', 'gender' => 'Femenino'],
                ['first' => 'Juan', 'middle' => 'David', 'surname' => 'Martín', 'second' => 'Gramajo', 'dob' => '2009-09-06', 'cui' => '2074518260108', 'code' => 'I533KQQ', 'gender' => 'Masculino'],
                ['first' => 'Jorge', 'middle' => 'Eduardo', 'surname' => 'Monterroso', 'second' => 'Gregorio', 'dob' => '2011-01-04', 'cui' => '2198294540101', 'code' => 'H494UZQ', 'gender' => 'Masculino'],
                ['first' => 'Claudia', 'middle' => 'Leticia', 'surname' => 'Méndez', 'second' => 'Aguilar', 'dob' => '2012-09-30', 'cui' => '2524882880101', 'code' => 'K800MZN', 'gender' => 'Femenino'],
                ['first' => 'Oliver', 'middle' => 'Fernando', 'surname' => 'Méndez', 'second' => 'Dealtán', 'dob' => '2007-08-25', 'cui' => '3381607360921', 'code' => 'G768UCU', 'gender' => 'Masculino'],
                ['first' => 'Beverly', 'middle' => 'Yanely', 'surname' => 'Osorio', 'second' => 'Soto', 'dob' => '2011-11-22', 'cui' => '2330517910101', 'code' => 'J940LTU', 'gender' => 'Femenino'],
                ['first' => 'Nuria', 'middle' => 'Melisa', 'surname' => 'Pascual', 'second' => 'Pascual', 'dob' => '2011-05-22', 'cui' => '2370181601317', 'code' => 'K701YTL', 'gender' => 'Femenino'],
                ['first' => 'José', 'middle' => 'Lisandro', 'surname' => 'Rodas', 'second' => 'Zet', 'dob' => '2008-05-29', 'cui' => '2006407710101', 'code' => 'E032WEL', 'gender' => 'Masculino'],
                ['first' => 'Juan', 'middle' => 'Benancio', 'surname' => 'Rodríguez', 'second' => 'Chaicoj', 'dob' => '2011-11-02', 'cui' => '2322504560108', 'code' => 'K401ICX', 'gender' => 'Masculino'],
                ['first' => 'Ana', 'middle' => 'Guadalupe', 'surname' => 'Rodríguez', 'second' => 'Equite', 'dob' => '1986-03-05', 'cui' => '2585228870101', 'code' => 'R218ZMA', 'gender' => 'Femenino'],
                ['first' => 'María', 'middle' => 'Fernanda', 'surname' => 'Rosales', 'second' => 'Osorio', 'dob' => '2009-02-05', 'cui' => '2028258091013', 'code' => 'F373UMQ', 'gender' => 'Femenino'],
                ['first' => 'Yessenia', 'middle' => 'Yajaira', 'surname' => 'Sajvin', 'second' => 'Culajay', 'dob' => '2006-06-22', 'cui' => '3911621440101', 'code' => 'F128VBQ', 'gender' => 'Femenino'],
                ['first' => 'Kimberly', 'middle' => 'Tatiana', 'surname' => 'Salazar', 'second' => 'Pérez', 'dob' => '2012-02-28', 'cui' => '2397292850101', 'code' => 'K300GSE', 'gender' => 'Femenino'],
                ['first' => 'Sara', 'middle' => 'Nohemi', 'surname' => 'Terraza', 'second' => 'Hernández', 'dob' => '2012-04-25', 'cui' => '2425180950101', 'code' => 'K600EEA', 'gender' => 'Femenino'],
                ['first' => 'Kevin', 'middle' => 'Josué', 'surname' => 'Trinidad', 'second' => 'Hernandez', 'dob' => '2010-08-24', 'cui' => '2210812750101', 'code' => 'I837WQD', 'gender' => 'Masculino'],
                ['first' => 'Meyli', 'middle' => 'Dayana', 'surname' => 'Tzay', 'second' => 'Choc', 'dob' => '2011-04-06', 'cui' => '2220657020407', 'code' => 'H180EPT', 'gender' => 'Femenino'],
                ['first' => 'Angel', 'middle' => 'Adrián', 'surname' => 'Velasquez', 'second' => 'Dávila', 'dob' => '2008-02-21', 'cui' => '2143702240101', 'code' => 'F982PAH', 'gender' => 'Masculino'],
                ['first' => 'Francis', 'middle' => 'Noemí', 'surname' => 'Xocoy', 'second' => 'De Paz', 'dob' => '2011-10-09', 'cui' => '2304579240101', 'code' => 'J847XEW', 'gender' => 'Femenino'],
                ['first' => 'Jade', 'middle' => 'Jimena', 'surname' => 'Yax', 'second' => 'Ajanel', 'dob' => '2012-02-11', 'cui' => '2397304370108', 'code' => 'K700JFM', 'gender' => 'Femenino'],
            ],
            4 => [
                ['first' => 'Pamela', 'middle' => 'Esmeralda', 'surname' => 'Aguilar', 'second' => 'Chávez', 'dob' => '2010-12-24', 'cui' => '2195866750101', 'code' => 'J344GQL', 'gender' => 'Femenino'],
                ['first' => 'Génesis', 'middle' => 'Nicole', 'surname' => 'Asún', 'second' => 'Morales', 'dob' => '2009-05-17', 'cui' => '2045674910101', 'code' => 'H899TKM', 'gender' => 'Femenino'],
                ['first' => 'Maria', 'middle' => 'Isabel', 'surname' => 'Bac', 'second' => 'Icó', 'dob' => '2009-01-14', 'cui' => '2026615711010', 'code' => 'I734SVW', 'gender' => 'Femenino'],
                ['first' => 'Alex', 'middle' => 'Baudilio', 'surname' => 'Barán', 'second' => 'Suar', 'dob' => '2011-01-08', 'cui' => '2195716860101', 'code' => 'J347YHP', 'gender' => 'Masculino'],
                ['first' => 'Ludwin', 'middle' => 'Alexander Nicolas', 'surname' => 'Cabinal', 'second' => 'Romero', 'dob' => '2008-12-14', 'cui' => '2039474950101', 'code' => 'E044SXN', 'gender' => 'Masculino'],
                ['first' => 'Paulina', 'middle' => 'Leticia', 'surname' => 'Cajbón', 'second' => 'López', 'dob' => '2009-09-24', 'cui' => '2064022520101', 'code' => 'I032VSE', 'gender' => 'Femenino'],
                ['first' => 'Ludwin', 'middle' => 'Domingo', 'surname' => 'Cho', 'second' => 'Xol', 'dob' => '2010-09-26', 'cui' => '2143810801607', 'code' => 'I549QPJ', 'gender' => 'Masculino'],
                ['first' => 'Alexia', 'middle' => 'Nahomy', 'surname' => 'Choc', 'second' => 'Carrasco', 'dob' => '2011-01-28', 'cui' => '2397762240101', 'code' => 'J232LSY', 'gender' => 'Femenino'],
                ['first' => 'Yosef', 'middle' => 'Alejandro', 'surname' => 'Fuentes', 'second' => 'Alvarado', 'dob' => '2009-06-04', 'cui' => '2046803440108', 'code' => 'H383AWK', 'gender' => 'Masculino'],
                ['first' => 'Sara', 'middle' => 'Esther', 'surname' => 'García', 'second' => 'Pineda', 'dob' => '2003-01-08', 'cui' => '3034304780108', 'code' => 'F532SZF', 'gender' => 'Femenino'],
                ['first' => 'William', 'middle' => 'Gabriel', 'surname' => 'Gómez', 'second' => 'Rabanales', 'dob' => '2009-08-18', 'cui' => '2059198040101', 'code' => 'H631YJL', 'gender' => 'Masculino'],
                ['first' => 'Brenda', 'middle' => 'Leticia', 'surname' => 'Loch', 'second' => 'Coc', 'dob' => '2010-04-10', 'cui' => '2111292750409', 'code' => 'J946TVB', 'gender' => 'Femenino'],
                ['first' => 'Mari', 'middle' => 'Magali', 'surname' => 'Oxlaj', 'second' => 'González', 'dob' => '2011-04-04', 'cui' => '2254686540101', 'code' => 'J647TXE', 'gender' => 'Femenino'],
                ['first' => 'Linda', 'middle' => 'Elizabeth', 'surname' => 'Ramirez', 'second' => 'Taxcón', 'dob' => '2009-07-14', 'cui' => '2049967320502', 'code' => 'I532RVP', 'gender' => 'Femenino'],
                ['first' => 'Darlyn', 'middle' => 'Pilar', 'surname' => 'Ramos', 'second' => 'Felipe', 'dob' => '2009-06-30', 'cui' => '2064090870101', 'code' => 'I437ADA', 'gender' => 'Femenino'],
                ['first' => 'Shirlen', 'middle' => 'Genesis Daniela', 'surname' => 'Rodríguez', 'second' => 'Segura', 'dob' => '2009-01-12', 'cui' => '2718471230108', 'code' => 'I726WKU', 'gender' => 'Femenino'],
                ['first' => 'David', 'middle' => 'Alexander', 'surname' => 'Soto', 'second' => 'Maldonado', 'dob' => '2010-09-20', 'cui' => '2153247490101', 'code' => 'I335RJQ', 'gender' => 'Masculino'],
                ['first' => 'Luis', 'middle' => 'Angel', 'surname' => 'Tiniguar', 'second' => 'Chaicoj', 'dob' => '2009-08-27', 'cui' => '2103789480101', 'code' => 'J743BMM', 'gender' => 'Masculino'],
                ['first' => 'Julia', 'middle' => 'Esmeralda Nohemi', 'surname' => 'Tobias', 'second' => 'Ajuchán', 'dob' => '2008-12-30', 'cui' => '2031674490101', 'code' => 'H532QYQ', 'gender' => 'Femenino'],
                ['first' => 'Sandra', 'middle' => 'Melissa', 'surname' => 'Xol', 'second' => 'Chub', 'dob' => '2011-03-10', 'cui' => '2201427261616', 'code' => 'J146JGH', 'gender' => 'Femenino'],
            ],
            5 => [
                ['first' => 'Durwin', 'middle' => 'Alexander Alberto', 'surname' => 'Aguilar', 'second' => 'Acabal', 'dob' => '2008-10-08', 'cui' => '2018146330101', 'code' => 'G070QLL', 'gender' => 'Masculino'],
                ['first' => 'Antoni', 'middle' => 'Alexander', 'surname' => 'Aguilar', 'second' => 'Vásquez', 'dob' => '2008-04-15', 'cui' => '2426774600101', 'code' => 'H553TRY', 'gender' => 'Masculino'],
                ['first' => 'Judith', 'middle' => 'Yajaira', 'surname' => 'Albizurez', 'second' => 'Hernández', 'dob' => '2008-04-05', 'cui' => '3033829530108', 'code' => 'F177JCD', 'gender' => 'Femenino'],
                ['first' => 'Heydi', 'middle' => 'Yamilet', 'surname' => 'Bautista', 'second' => 'Sequen', 'dob' => '2007-11-24', 'cui' => '3022702210101', 'code' => 'F794CKU', 'gender' => 'Femenino'],
                ['first' => 'Donaldson', 'middle' => 'Arturo Kevis', 'surname' => 'Caal', 'second' => 'Choc', 'dob' => '2005-02-14', 'cui' => '3145850011420', 'code' => 'D237JGM', 'gender' => 'Masculino'],
                ['first' => 'María', 'middle' => 'Angel', 'surname' => 'Castañeda', 'second' => 'Meza', 'dob' => '2008-12-16', 'cui' => '2028324490115', 'code' => 'E234WRX', 'gender' => 'Femenino'],
                ['first' => 'Krisbel', 'middle' => 'Yasmin', 'surname' => 'Duarte', 'second' => 'Trinidad', 'dob' => '2008-03-26', 'cui' => '2002141872011', 'code' => 'G135TDF', 'gender' => 'Femenino'],
                ['first' => 'Brayan', 'middle' => 'Alexander', 'surname' => 'Hernández', 'second' => 'Baten', 'dob' => '2008-07-14', 'cui' => '3041685050112', 'code' => 'H328KQG', 'gender' => 'Masculino'],
                ['first' => 'Brandon', 'middle' => 'Jossúe', 'surname' => 'López', 'second' => 'García', 'dob' => '2010-11-23', 'cui' => '2252710790101', 'code' => 'E275LAQ', 'gender' => 'Masculino'],
                ['first' => 'Elena', 'middle' => 'Giselle', 'surname' => 'López', 'second' => 'Talavera', 'dob' => '2009-08-25', 'cui' => '2058932010106', 'code' => 'H754KCX', 'gender' => 'Femenino'],
                ['first' => 'Kimberlin', 'middle' => 'Cecilia', 'surname' => 'López', 'second' => 'Y López', 'dob' => '2007-05-02', 'cui' => '3604736600108', 'code' => 'I528QSA', 'gender' => 'Femenino'],
                ['first' => 'Oscar', 'middle' => 'Natanael', 'surname' => 'Miron', 'second' => 'Osorio', 'dob' => '2010-06-21', 'cui' => '2148183220108', 'code' => 'H950XTE', 'gender' => 'Masculino'],
                ['first' => 'Stefani', 'middle' => 'Gricelda', 'surname' => 'Méndez', 'second' => 'Pablo', 'dob' => '2008-04-26', 'cui' => '3249076561331', 'code' => 'H988ZWR', 'gender' => 'Femenino'],
                ['first' => 'Mariana', 'middle' => 'Rosmeri', 'surname' => 'Pascual', 'second' => 'Pascual', 'dob' => '2008-07-07', 'cui' => '3202747561317', 'code' => 'G179BBS', 'gender' => 'Femenino'],
                ['first' => 'Angie', 'middle' => 'Anaely', 'surname' => 'Pirir', 'second' => 'Miranda', 'dob' => '2008-12-23', 'cui' => '2026045350108', 'code' => 'H175YLF', 'gender' => 'Femenino'],
                ['first' => 'Sergio', 'middle' => 'Eliseo', 'surname' => 'Quintana', 'second' => 'Us', 'dob' => '2010-03-06', 'cui' => '2102232510108', 'code' => 'J047IIC', 'gender' => 'Masculino'],
                ['first' => 'Carlos', 'middle' => 'Alexander', 'surname' => 'Racanac', 'second' => 'Hernández', 'dob' => '2008-11-26', 'cui' => '2019768460504', 'code' => 'F867ZAF', 'gender' => 'Masculino'],
                ['first' => 'Jennifer', 'middle' => 'Mishelle', 'surname' => 'Rodríguez', 'second' => 'Segura', 'dob' => '2000-10-04', 'cui' => '3021723000101', 'code' => 'C114XEE', 'gender' => 'Femenino'],
                ['first' => 'Oscar', 'middle' => 'Santana', 'surname' => 'Sirin', 'second' => 'Pelico', 'dob' => '2008-01-26', 'cui' => '3275169761020', 'code' => 'G066DSD', 'gender' => 'Masculino'],
                ['first' => 'Mynor', 'middle' => 'Omar', 'surname' => 'Soliz', 'second' => 'Coronado', 'dob' => '2003-05-19', 'cui' => '3022285780101', 'code' => 'E894LDG', 'gender' => 'Masculino'],
            ],
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
