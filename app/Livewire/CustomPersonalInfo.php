<?php

namespace App\Livewire;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Jeffgreco13\FilamentBreezy\Livewire\PersonalInfo;

class CustomPersonalInfo extends PersonalInfo
{
    public array $only = [
        'name',
        'email',
        'whatsapp_number',
        'job_title',
        'profile_photo',
    ];

    protected function getProfileFormComponents(): array
    {
        return [
            $this->getNameComponent(),
            $this->getEmailComponent(),

            TextInput::make('whatsapp_number')->label('No. WhatsApp')->tel(),
            TextInput::make('job_title')->label('Jabatan'),

            FileUpload::make('profile_photo')
                ->label('Foto Profil')
                ->disk('public')
                ->directory('profile-photos')
                ->image(),
        ];
    }
}
