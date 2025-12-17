<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([

                // =========================
                // SUBSCRIPTION DETAILS
                // =========================
                Section::make('Subscription Details')
                    ->description('Pengaturan langganan notifikasi pengguna')
                    ->icon('heroicon-o-bell')
                    ->collapsed(false)
                    ->schema([

                        Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Pilih pengguna yang berlangganan notifikasi.'),

                        Toggle::make('notify_email')
                            ->label('Email Notification')
                            ->helperText('Kirim notifikasi melalui email.')
                            ->default(true)
                            ->required(),

                        Toggle::make('notify_whatsapp')
                            ->label('WhatsApp Notification')
                            ->helperText('Kirim notifikasi melalui WhatsApp.')
                            ->default(false)
                            ->required(),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 3,
                    ]),
            ]);
    }
}
