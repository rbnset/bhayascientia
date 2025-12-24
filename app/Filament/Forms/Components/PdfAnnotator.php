<?php

namespace App\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;

class PdfAnnotator extends Field
{
    protected string $view = 'filament.forms.components.pdf-annotator';

    protected string | Closure | null $fileUrl = null;

    public function fileUrl(string|Closure|null $fileUrl): static
    {
        $this->fileUrl = $fileUrl;

        return $this;
    }

    public function getFileUrl(): ?string
    {
        return $this->evaluate($this->fileUrl);
    }
}
