<?php
// app/Filament/Resources/DocumentVerifications/Pages/EditDocumentVerification.php

namespace App\Filament\Resources\DocumentVerifications\Pages;

use App\Filament\Resources\DocumentVerifications\DocumentVerificationResource;
use Filament\Resources\Pages\EditRecord;

class EditDocumentVerification extends EditRecord
{
    protected static string $resource = DocumentVerificationResource::class;
}
