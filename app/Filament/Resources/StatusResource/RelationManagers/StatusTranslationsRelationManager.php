<?php

namespace App\Filament\Resources\StatusResource\RelationManagers;

use App\Enum\LangCode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;


class StatusTranslationsRelationManager extends RelationManager
{
    protected static string $relationship = 'statusTranslations';

    protected static ?string $title = 'Status tarjimalari';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\Select::make('lang_code')
                        ->label("Til kodi")
                        ->required()
                        ->options(LangCode::getList())
                        ->default(LangCode::UZ),
                    Forms\Components\TextInput::make('name')
                        ->label("Sarlavha")
                        ->required()
                        ->maxLength(255),
                    Forms\Components\RichEditor::make('message')
                        ->label("Matn")
                        ->required(),
                ])
            ]);

    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label("Tarjima qo'shish"),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label("Tahrirlash"),
                Tables\Actions\DeleteAction::make()
                    ->label("O'chirish"),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label("Tanlanganlarni o'chirish"),
                ]),
            ]);
    }
}
