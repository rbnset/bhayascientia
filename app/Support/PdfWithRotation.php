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
    // Tambahan: Rounded Rectangle untuk header stamp
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

    // ─────────────────────────────────────────────────────────────
    // Circle & Arc untuk watermark
    // ─────────────────────────────────────────────────────────────
    public function Circle(float $x, float $y, float $r, string $style = 'D'): void
    {
        $this->Ellipse($x, $y, $r, $r, $style);
    }

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

    public function Arc(
        float $x,
        float $y,
        float $r,
        float $aStart,
        float $aEnd,
        string $style = ''
    ): void {
        $aStart = deg2rad($aStart);
        $aEnd   = deg2rad($aEnd);

        $k  = $this->k;
        $h  = $this->h;

        $cx = $x + $r * cos($aStart);
        $cy = $y - $r * sin($aStart);

        $this->_out(sprintf('%.2F %.2F m', $cx * $k, ($h - $cy) * $k));

        $step    = deg2rad(30);
        $current = $aStart;

        while ($current < $aEnd) {
            $next   = min($current + $step, $aEnd);
            $mid    = ($current + $next) / 2;
            $factor = (4 / 3) * tan(($next - $current) / 4);

            $x1 = $x + $r * cos($current) - $factor * $r * sin($current);
            $y1 = $y - ($r * sin($current) + $factor * $r * cos($current));
            $x2 = $x + $r * cos($next) + $factor * $r * sin($next);
            $y2 = $y - ($r * sin($next) - $factor * $r * cos($next));
            $x3 = $x + $r * cos($next);
            $y3 = $y - $r * sin($next);

            $this->_out(sprintf(
                '%.2F %.2F %.2F %.2F %.2F %.2F c',
                $x1 * $k,
                ($h - $y1) * $k,
                $x2 * $k,
                ($h - $y2) * $k,
                $x3 * $k,
                ($h - $y3) * $k
            ));

            $current = $next;
        }

        if (filled($style)) {
            $this->_out('S');
        }
    }
}
