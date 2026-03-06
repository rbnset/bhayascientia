<?php
// app/Filament/Resources/DocumentVerifications/Schemas/DocumentVerificationForm.php

namespace App\Filament\Resources\DocumentVerifications\Schemas;

use Filament\Schemas\Schema;

class DocumentVerificationForm
{
    public static function configure(Schema $schema): Schema
    {
        // Form dibiarkan kosong — resource ini read-only
        return $schema->components([]);
    }
}
