<?php

namespace App\Filament\Resources\Publications\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PublicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('publication_type_id')
                    ->required()
                    ->numeric(),
                TextInput::make('title')
                    ->required(),
                Textarea::make('abstract')
                    ->columnSpanFull(),
                FileUpload::make('cover_image_path')
                    ->image(),
                TextInput::make('category_id')
                    ->numeric(),
                TextInput::make('method_id')
                    ->numeric(),
                Select::make('status')
                    ->options([
            'draft' => 'Draft',
            'submitted' => 'Submitted',
            'in_review' => 'In review',
            'revision_required' => 'Revision required',
            'accepted' => 'Accepted',
            'rejected' => 'Rejected',
            'published' => 'Published',
        ])
                    ->default('draft')
                    ->required(),
                DateTimePicker::make('published_at'),
            ]);
    }
}
