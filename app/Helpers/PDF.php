<?php

namespace App\Helpers;

use App\Models\User;
use FPDF;

class PDF extends FPDF
{
    public bool $hideFooter = false;

    public function addImage($path, $x = null, $y = null, $w = 0, $h = 0)
    {
        $imagePath = public_path($path);
        if (file_exists($imagePath)) {
            $this->Image($imagePath, $x, $y, $w, $h);
        }
    }

    public function CellUTF8($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '')
    {
        $txt = $this->convertToWindowsCharset($txt);
        $this->Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
    }

    public function MultiCellUTF8($w, $h, $txt, $border = 0, $align = 'J', $fill = false)
    {
        $txt = $this->convertToWindowsCharset($txt);
        $this->MultiCell($w, $h, $txt, $border, $align, $fill);
    }

    private function convertToWindowsCharset($txt)
    {
        if ($txt === null || $txt === '') {
            return '';
        }

        // Mapeo manual de caracteres UTF-8 a ISO-8859-1 (el formato que entiende FPDF)
        // Esto evita usar mb_convert_encoding o utf8_decode que están en riesgo
        $utf8_to_iso8859_1 = [
            "\xc3\xa1" => "\xe1",
            "\xc3\xa9" => "\xe9",
            "\xc3\xad" => "\xed",
            "\xc3\xb3" => "\xf3",
            "\xc3\xba" => "\xfa", // á, é, í, ó, ú
            "\xc3\x81" => "\xc1",
            "\xc3\x89" => "\xc9",
            "\xc3\x8d" => "\xcd",
            "\xc3\x93" => "\xd3",
            "\xc3\x9a" => "\xda", // Á, É, Í, Ó, Ú
            "\xc3\xb1" => "\xf1",
            "\xc3\x91" => "\xd1", // ñ, Ñ
            "\xc3\xbc" => "\xfc",
            "\xc3\x9c" => "\xdc", // ü, Ü
            "\xc2\xbf" => "\xbf",
            "\xc2\xa1" => "\xa1", // ¿, ¡
            "\xc2\xba" => "\xba",
            "\xc2\xaa" => "\xaa", // º, ª
        ];

        // Primero intentamos la conversión con iconv (que es el estándar actual recomendado)
        // Si no está disponible o falla, usamos el mapeo manual.
        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $txt);
            if ($converted !== false) {
                return $converted;
            }
        }

        return strtr($txt, $utf8_to_iso8859_1);
    }

    // =====================================
    // FOOTER Y VIÑETAS (Integrado)
    // =====================================
    public function Footer()
    {
        if ($this->hideFooter) return;
        
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetLineWidth(0.5);
        $this->Line(10, 266, 200, 266);

        date_default_timezone_set('America/Guatemala');

        $this->Cell(63, 10, date('d/m/Y'), 0, 0, 'C');
        $this->Cell(64, 10, date('H:i:sa'), 0, 0, 'C');
        $this->CellUTF8(63, 10, 'Página ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    public function MultiCellBlt($w, $h, $blt, $txt, $border = 0, $align = 'J', $fill = false)
    {
        $blt_width = $this->GetStringWidth($blt) + $this->cMargin * 2;
        $bak_x = $this->GetX();
        $this->CellUTF8($blt_width, $h, $blt, 0, '', $fill);
        $this->MultiCellUTF8($w - $blt_width, $h, $txt, $border, $align, $fill);
        $this->SetX($bak_x);
    }

    // =====================================
    // TUS FUNCIONES DE DIBUJO AVANZADAS
    // =====================================
    public function getDocumentStatus(User $user, string $documentName)
    {
        $document = $user->documents()->where('name', $documentName)->first();
        return $document && $document->physical ? "1" : "0";
    }

    public function Row($data, $width, $height, $border = 1, $align = 'C', $fill = false)
    {
        $startX = $this->GetX();
        $startY = $this->GetY();
        $maxY = $startY;

        foreach ($data as $i => $text) {
            $this->SetXY($startX, $startY);
            $this->MultiCellUTF8($width[$i], $height[$i], $text, $border, $align, $fill);
            $startX += $width[$i];

            $currentY = $this->GetY();
            if ($currentY > $maxY) {
                $maxY = $currentY;
            }
        }
        $this->SetY($maxY);
    }

    public function RoundedRect($x, $y, $w, $h, $r, $style = '')
    {
        if ($r < 0) $r = 0;
        if ($r > min($w, $h) / 2) $r = min($w, $h) / 2;

        switch ($style) {
            case 'F':
                $op = 'f';
                break;
            case 'FD':
            case 'DF':
                $op = 'B';
                break;
            default:
                $op = 'S';
                break;
        }

        $MyArc = 4 / 3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m', ($x + $r) * $this->k, ($this->h - $y) * $this->k));
        $this->_out(sprintf('%.2F %.2F l', ($x + $w - $r) * $this->k, ($this->h - $y) * $this->k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c', ($x + $w - $r + $r * $MyArc) * $this->k, ($this->h - $y) * $this->k, ($x + $w) * $this->k, ($this->h - $y - $r + $r * $MyArc) * $this->k, ($x + $w) * $this->k, ($this->h - $y - $r) * $this->k));
        $this->_out(sprintf('%.2F %.2F l', ($x + $w) * $this->k, ($this->h - $y - $h + $r) * $this->k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c', ($x + $w) * $this->k, ($this->h - $y - $h + $r - $r * $MyArc) * $this->k, ($x + $w - $r + $r * $MyArc) * $this->k, ($this->h - $y - $h) * $this->k, ($x + $w - $r) * $this->k, ($this->h - $y - $h) * $this->k));
        $this->_out(sprintf('%.2F %.2F l', ($x + $r) * $this->k, ($this->h - $y - $h) * $this->k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c', ($x + $r - $r * $MyArc) * $this->k, ($this->h - $y - $h) * $this->k, $x * $this->k, ($this->h - $y - $h + $r - $r * $MyArc) * $this->k, $x * $this->k, ($this->h - $y - $h + $r) * $this->k));
        $this->_out(sprintf('%.2F %.2F l', $x * $this->k, ($this->h - $y - $r) * $this->k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c', $x * $this->k, ($this->h - $y - $r + $r * $MyArc) * $this->k, ($x + $r - $r * $MyArc) * $this->k, ($this->h - $y) * $this->k, ($x + $r) * $this->k, ($this->h - $y) * $this->k));
        $this->_out($op);
    }

    public function calculateTextHeight($text, $width, $lineHeight = 4)
    {
        $textWidth = $this->GetStringWidth($text);
        $lines = ceil($textWidth / ($width * 3.5));
        $lines = max(1, $lines);
        return $lines * $lineHeight;
    }

    public function drawDynamicField($labelX, $fieldX, $y, $labelWidth, $fieldWidth, $label, $text, $minHeight = 7)
    {
        $textHeight = $this->calculateTextHeight($text, $fieldWidth);
        $fieldHeight = max($minHeight, $textHeight + 2);

        $this->SetXY($labelX, $y);
        $this->CellUTF8($labelWidth, $fieldHeight, $label, 0, 0, 'R');

        $this->RoundedRect($fieldX, $y, $fieldWidth, $fieldHeight, 3);

        $this->SetXY($fieldX, $y);
        $this->MultiCellUTF8($fieldWidth, 4, $text, 0, "C", false);

        return $fieldHeight;
    }

    /**
     * Inserta una imagen a prueba de fallos.
     * Convierte cualquier formato (PNG, WebP, extensiones falsas) a un JPG temporal 
     * para que FPDF no colapse.
     */
    public function addSafeImage($absolutePath, $x, $y, $w = 0, $h = 0)
    {
        if (!file_exists($absolutePath) || is_dir($absolutePath)) {
            return;
        }

        // Crear una ruta temporal segura
        $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('pdf_img_') . '.jpg';

        $imageString = @file_get_contents($absolutePath);
        if (!$imageString) return;

        // PHP 8+ crea un objeto GdImage automáticamente
        $img = @imagecreatefromstring($imageString);

        if ($img instanceof \GdImage) {
            $ancho = imagesx($img);
            $alto = imagesy($img);

            // Creamos el lienzo (también es un objeto GdImage)
            $lienzo = imagecreatetruecolor($ancho, $alto);
            $blanco = imagecolorallocate($lienzo, 255, 255, 255);
            imagefill($lienzo, 0, 0, $blanco);

            imagecopy($lienzo, $img, 0, 0, 0, 0, $ancho, $alto);

            // Guardar como JPG
            imagejpeg($lienzo, $tempPath, 100);

            // YA NO USAMOS imagedestroy(). 
            // Simplemente liberamos las variables y el recolector de basura hace el resto.
            $img = null;
            $lienzo = null;

            // Insertar en FPDF
            $this->Image($tempPath, $x, $y, $w, $h, 'JPG');

            // Borrar archivo físico temporal
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }
    }

    /**
     * Alias corto para convertToWindowsCharset
     */
    public function dec($txt)
    {
        return $this->convertToWindowsCharset($txt);
    }

    public function rotatedHeader(float $x, float $y, float $w, float $h, string $text): void
    {
        $currentY = $this->GetY();
        $this->SetXY($x, $y);
        $this->Cell($w, $h, '', 1, 0, 'C', true);

        $angle = 90 * M_PI / 180;
        $c  = cos($angle);
        $s  = sin($angle);
        $textX = $x + $w / 2 + 1.5;
        $textY = $y + $h - 1;
        $cx = $textX * $this->k;
        $cy = ($this->h - $textY) * $this->k;

        $this->_out(sprintf(
            'q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',
            $c,
            $s,
            -$s,
            $c,
            $cx,
            $cy,
            -$cx,
            -$cy
        ));
        $this->Text($textX, $textY, $text);
        $this->_out('Q');

        $this->SetY($currentY);
    }
}
