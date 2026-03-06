<?php
// app/Filament/Resources/DocumentVerifications/Pages/CreateDocumentVerification.php

namespace App\Filament\Resources\DocumentVerifications\Pages;

use App\Filament\Resources\DocumentVerifications\DocumentVerificationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDocumentVerification extends CreateRecord
{
    protected static string $resource = DocumentVerificationResource::class;
}
