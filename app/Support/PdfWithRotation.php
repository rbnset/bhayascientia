<?php

namespace App\Support;

use setasign\Fpdi\Fpdi;

class PdfWithRotation extends Fpdi
{
    protected float $angle = 0;

    public function Rotate(float $angle, float $x = -1, float $y = -1): void
    {
        if ($x === -1) {
            $x = $this->x;
        }
        if ($y === -1) {
            $y = $this->y;
        }

        if ($this->angle !== 0) {
            $this->_out('Q');
        }

        $this->angle = $angle;

        if ($angle !== 0) {
            $angle *= M_PI / 180;
            $c = cos($angle);
            $s = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;

            $this->_out(sprintf(
                'q %.5F %.5F %.5F %.5F %.5F %.5F cm 1 0 0 1 %.5F %.5F cm',
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

    public function RotatedText(float $x, float $y, string $txt, float $angle): void
    {
        $this->Rotate($angle, $x, $y);
        $this->Text($x, $y, $txt);
        $this->Rotate(0);
    }
}
