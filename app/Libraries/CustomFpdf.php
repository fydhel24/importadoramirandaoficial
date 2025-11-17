<?php

namespace App\Libraries;

use Fpdf\Fpdf;

class CustomFpdf extends Fpdf
{
    protected $angle = 0;

    /**
     * Rotar elementos en el PDF.
     */
    public function Rotate($angle, $x = -1, $y = -1)
    {
        if ($x == -1) {
            $x = $this->x;
        }
        if ($y == -1) {
            $y = $this->y;
        }
        if ($this->angle != 0) {
            $this->_out('Q');
        }
        $this->angle = $angle;
        if ($angle != 0) {
            $angle *= M_PI / 180;
            $c = cos($angle);
            $s = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;
            $this->_out(sprintf('q %.3F %.3F %.3F %.3F %.3F %.3F cm 1 0 0 1 %.3F %.3F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
        }
    }


    public function Sector($x, $y, $r, $aStart, $aEnd, $style = 'FD', $cw = true, $o = 90)
    {
        $d0 = $aStart + $o;
        $d1 = $aEnd + $o;
        $d0 = $cw ? $d0 : $d1;
        $d1 = $cw ? $d1 : $d0;
        $aStart = min($d0, $d1);
        $aEnd = max($d0, $d1);
        $aStart = $aStart % 360;
        $aEnd = $aEnd % 360;
        if ($aStart > $aEnd) {
            $aEnd += 360;
        }
        $aStart = deg2rad($aStart);
        $aEnd = deg2rad($aEnd);

        $this->SetXY($x, $y);
        $this->Line($this->x, $this->y, $x + $r * cos($aStart), $y - $r * sin($aStart));
        $this->_Arc($x, $y, $r, $aStart, $aEnd, $style);
        $this->Line($this->x, $this->y, $x, $y);
        if ($style == 'F' || $style == 'FD' || $style == 'DF') {
            $this->_out('f');
        }
    }

    /**
     * Dibujar un arco en el PDF.
     *
     * @param float $x Coordenada X del centro.
     * @param float $y Coordenada Y del centro.
     * @param float $r Radio del arco.
     * @param float $aStart Ángulo inicial en radianes.
     * @param float $aEnd Ángulo final en radianes.
     * @param string $style Estilo del arco ('F', 'D', 'FD', 'DF').
     */
    protected function _Arc($x, $y, $r, $aStart, $aEnd, $style)
    {
        $k = $this->k;
        $hp = $this->h;
        $op = $this->_getStyle($style);

        $xStart = $x + $r * cos($aStart);
        $yStart = $y - $r * sin($aStart);
        $xEnd = $x + $r * cos($aEnd);
        $yEnd = $y - $r * sin($aEnd);

        $this->_out(sprintf('%.2F %.2F m', $xStart * $k, ($hp - $yStart) * $k));
        $this->_out(sprintf(
            '%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x + $r * cos($aStart + M_PI / 4)) * $k,
            ($hp - ($y - $r * sin($aStart + M_PI / 4))) * $k,
            ($x + $r * cos($aEnd - M_PI / 4)) * $k,
            ($hp - ($y - $r * sin($aEnd - M_PI / 4))) * $k,
            $xEnd * $k,
            ($hp - $yEnd) * $k
        ));
    }
    /**
     * Dibujar un arco en el PDF.
     *
     * @param float $x Coordenada X del centro.
     * @param float $y Coordenada Y del centro.
     * @param float $r Radio del arco.
     * @param float $aStart Ángulo inicial en grados.
     * @param float $aEnd Ángulo final en grados.
     * @param string $style Estilo del arco ('F', 'D', 'FD', 'DF').
     */
    protected function addPieChart($pdf, $data)
    {
        $pdf->Ln(10);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, utf8_decode('Gráfico Circular'), 0, 1, 'C');
        $pdf->Ln(5);

        $xCenter = 105; // Centro del círculo en X
        $yCenter = 100; // Centro del círculo en Y
        $radius = 40; // Radio del círculo

        $total = array_sum($data);
        $startAngle = 0;

        foreach ($data as $label => $value) {
            $angle = ($value / $total) * 360;
            $endAngle = $startAngle + $angle;

            // Colores aleatorios para cada sector
            $color = [rand(50, 200), rand(50, 200), rand(50, 200)];
            $pdf->SetFillColor($color[0], $color[1], $color[2]);

            // Dibujar el sector
            $pdf->Sector($xCenter, $yCenter, $radius, $startAngle, $endAngle, 'F');
            $startAngle = $endAngle;
        }

        // Leyenda
        $pdf->Ln(10);
        foreach ($data as $label => $value) {
            $pdf->Cell(0, 10, utf8_decode("$label: $value"), 0, 1);
        }
    }
    protected function _getStyle($style)
    {
        switch (strtoupper($style)) {
            case 'F':
                return 'f';
            case 'D':
                return 'S';
            case 'FD':
            case 'DF':
                return 'B';
            default:
                return 'S';
        }
    }
}
