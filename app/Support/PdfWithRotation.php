<?php

namespace App\Support;

use setasign\Fpdi\Fpdi;

class PdfWithRotation extends Fpdi
{
    public float $angle = 0;

    // ─────────────────────────────────────────────────────────────
    // Rotasi teks (sudah ada, tidak berubah)
    // ─────────────────────────────────────────────────────────────
    public function Rotate(float $angle, float $x = -1, float $y = -1): void
    {
        if ($x == -1) $x = $this->x;
        if ($y == -1) $y = $this->y;

        if ($this->angle != 0) $this->_out('Q');

        $this->angle = $angle;

        if ($angle != 0) {
            $angle *= M_PI / 180;
            $c  = cos($angle);
            $s  = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;

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
        }
    }

    public function RotatedText(float $x, float $y, string $text, float $angle): void
    {
        $this->Rotate($angle, $x, $y);
        $this->Text($x, $y, $text);
        $this->Rotate(0);
    }

    protected function _endpage(): void
    {
        if ($this->angle != 0) {
            $this->angle = 0;
            $this->_out('Q');
        }
        parent::_endpage();
    }

    // ─────────────────────────────────────────────────────────────
    // Rounded Rectangle
    // ─────────────────────────────────────────────────────────────
    public function RoundedRect(
        float $x,
        float $y,
        float $w,
        float $h,
        float $r,
        string $style = ''
    ): void {
        $k  = $this->k;
        $hp = $this->h;

        if ($style === 'F') {
            $op = 'f';
        } elseif ($style === 'FD' || $style === 'DF') {
            $op = 'B';
        } else {
            $op = 'S';
        }

        $MyArc = 4 / 3 * (sqrt(2) - 1);

        $this->_out(sprintf('%.2F %.2F m', ($x + $r) * $k, ($hp - $y) * $k));

        $xc = $x + $w - $r;
        $yc = $y + $r;
        $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - $y) * $k));
        $this->_Arc($xc + $r * $MyArc, $yc - $r, $xc + $r, $yc - $r * $MyArc, $xc + $r, $yc);

        $xc = $x + $w - $r;
        $yc = $y + $h - $r;
        $this->_out(sprintf('%.2F %.2F l', ($x + $w) * $k, ($hp - $yc) * $k));
        $this->_Arc($xc + $r, $yc + $r * $MyArc, $xc + $r * $MyArc, $yc + $r, $xc, $yc + $r);

        $xc = $x + $r;
        $yc = $y + $h - $r;
        $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - ($y + $h)) * $k));
        $this->_Arc($xc - $r * $MyArc, $yc + $r, $xc - $r, $yc + $r * $MyArc, $xc - $r, $yc);

        $xc = $x + $r;
        $yc = $y + $r;
        $this->_out(sprintf('%.2F %.2F l', $x * $k, ($hp - $yc) * $k));
        $this->_Arc($xc - $r, $yc - $r * $MyArc, $xc - $r * $MyArc, $yc - $r, $xc, $yc - $r);

        $this->_out($op);
    }

    // ─────────────────────────────────────────────────────────────
    // Circle — lingkaran penuh
    // ─────────────────────────────────────────────────────────────
    public function Circle(float $x, float $y, float $r, string $style = 'D'): void
    {
        $this->Ellipse($x, $y, $r, $r, $style);
    }

    // ─────────────────────────────────────────────────────────────
    // Ellipse
    // ─────────────────────────────────────────────────────────────
    public function Ellipse(float $x, float $y, float $rx, float $ry, string $style = 'D'): void
    {
        if ($style === 'F') {
            $op = 'f';
        } elseif ($style === 'FD' || $style === 'DF') {
            $op = 'B';
        } else {
            $op = 'S';
        }

        $lx = 4 / 3 * (M_SQRT2 - 1) * $rx;
        $ly = 4 / 3 * (M_SQRT2 - 1) * $ry;
        $k  = $this->k;
        $h  = $this->h;

        $this->_out(sprintf('%.2F %.2F m', ($x + $rx) * $k, ($h - $y) * $k));
        $this->_out(sprintf(
            '%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x + $rx) * $k,
            ($h - ($y - $ly)) * $k,
            ($x + $lx) * $k,
            ($h - ($y - $ry)) * $k,
            $x * $k,
            ($h - ($y - $ry)) * $k
        ));
        $this->_out(sprintf(
            '%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x - $lx) * $k,
            ($h - ($y - $ry)) * $k,
            ($x - $rx) * $k,
            ($h - ($y - $ly)) * $k,
            ($x - $rx) * $k,
            ($h - $y) * $k
        ));
        $this->_out(sprintf(
            '%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x - $rx) * $k,
            ($h - ($y + $ly)) * $k,
            ($x - $lx) * $k,
            ($h - ($y + $ry)) * $k,
            $x * $k,
            ($h - ($y + $ry)) * $k
        ));
        $this->_out(sprintf(
            '%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x + $lx) * $k,
            ($h - ($y + $ry)) * $k,
            ($x + $rx) * $k,
            ($h - ($y + $ly)) * $k,
            ($x + $rx) * $k,
            ($h - $y) * $k
        ));
        $this->_out($op);
    }

    // ─────────────────────────────────────────────────────────────
    // Arc — busur lingkaran (dipakai untuk gambar gembok)
    // ─────────────────────────────────────────────────────────────
    public function Arc(
        float $cx,
        float $cy,
        float $r,
        float $startAngle,
        float $endAngle,
        string $style = 'D'
    ): void {
        $startRad = deg2rad($startAngle);
        $endRad   = deg2rad($endAngle);
        $k        = $this->k;
        $h        = $this->h;

        $x1 = $cx + $r * cos($startRad);
        $y1 = $cy - $r * sin($startRad);
        $this->_out(sprintf('%.2F %.2F m', $x1 * $k, ($h - $y1) * $k));

        $steps = max(1, (int) ceil(abs($endRad - $startRad) / (M_PI / 4)));
        $step  = ($endRad - $startRad) / $steps;

        for ($i = 0; $i < $steps; $i++) {
            $a0 = $startRad + $i * $step;
            $a1 = $a0 + $step;

            $alpha = sin($a1 - $a0) * (sqrt(4 + 3 * pow(tan(($a1 - $a0) / 2), 2)) - 1) / 3;

            $x0  = $cx + $r * cos($a0);
            $y0 = $cy - $r * sin($a0);
            $xe  = $cx + $r * cos($a1);
            $ye = $cy - $r * sin($a1);
            $dx0 = -$r * sin($a0);
            $dy0 = -$r * cos($a0);
            $dx1 = -$r * sin($a1);
            $dy1 = -$r * cos($a1);

            $this->_out(sprintf(
                '%.2F %.2F %.2F %.2F %.2F %.2F c',
                ($x0 + $alpha * $dx0) * $k,
                ($h - ($y0 + $alpha * $dy0)) * $k,
                ($xe - $alpha * $dx1) * $k,
                ($h - ($ye - $alpha * $dy1)) * $k,
                $xe * $k,
                ($h - $ye) * $k
            ));
        }

        if ($style !== '') {
            $this->_out('S');
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Internal Arc helper (untuk RoundedRect)
    // ─────────────────────────────────────────────────────────────
    private function _Arc(
        float $x1,
        float $y1,
        float $x2,
        float $y2,
        float $x3,
        float $y3
    ): void {
        $h = $this->h;
        $this->_out(sprintf(
            '%.2F %.2F %.2F %.2F %.2F %.2F c',
            $x1 * $this->k,
            ($h - $y1) * $this->k,
            $x2 * $this->k,
            ($h - $y2) * $this->k,
            $x3 * $this->k,
            ($h - $y3) * $this->k
        ));
    }
}
