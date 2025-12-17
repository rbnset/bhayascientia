<?php

namespace App\Filament\Resources\Reviews\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('publication_version_id')
                    ->required()
                    ->numeric(),
                TextInput::make('reviewer_id')
                    ->required()
                    ->numeric(),
                Select::make('decision')
                    ->options(['revision_required' => 'Revision required', 'accepted' => 'Accepted', 'rejected' => 'Rejected']),
                Textarea::make('overall_comment')
                    ->columnSpanFull(),
            ]);
    }
}
