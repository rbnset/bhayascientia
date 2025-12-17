<?php

namespace App\Filament\Resources\DownloadLogs\Pages;

use App\Filament\Resources\DownloadLogs\DownloadLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDownloadLog extends CreateRecord
{
    protected static string $resource = DownloadLogResource::class;
}
