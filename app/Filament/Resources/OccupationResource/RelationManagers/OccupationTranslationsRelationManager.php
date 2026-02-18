<?php

namespace App\Filament\Resources\OccupationResource\RelationManagers;

use App\Enum\LangCode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;


class OccupationTranslationsRelationManager extends RelationManager
{
    protected static string $relationship = 'occupationTranslations';
    protected static ?string $title = "Bo'sh ish o'rni tarjimalari";

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\Select::make('lang_code')
                        ->label("Tilni tanlang")
                        ->required()
                        ->options(LangCode::getList())
                        ->default(LangCode::UZ),
                    Forms\Components\TextInput::make('title')
                        ->label("Sarlavha")
                        ->required()
                        ->maxLength(255),
                    Forms\Components\RichEditor::make('description')
                        ->label("Matn")
                        ->required(),
                ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('lang_code')
                    ->label("Til")
                    ->formatStateUsing(fn ($state) => LangCode::name($state)),
                Tables\Columns\TextColumn::make('title')
                    ->label("Sarlavha"),
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
