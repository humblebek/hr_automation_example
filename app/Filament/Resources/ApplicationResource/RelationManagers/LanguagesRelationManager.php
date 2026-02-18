<?php

namespace App\Filament\Resources\ApplicationResource\RelationManagers;

use App\Enum\Lang;
use App\Enum\LanguageLevel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LanguagesRelationManager extends RelationManager
{
    protected static string $relationship = 'languages';

    protected static ?string $title = "Til qobilyati";

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('name')
                    ->label("Til")
                    ->required()
                    ->options(Lang::getList()),
                Forms\Components\Select::make('level')
                    ->label("Bilish darajasi")
                    ->required()
                    ->options(LanguageLevel::getList()),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label("Til")
                    ->formatStateUsing(fn($record) => Lang::name($record->name)),
                Tables\Columns\TextColumn::make('level')
                    ->label("Bilish darajasi")
                    ->formatStateUsing(fn($record) => LanguageLevel::name($record->level)),
            ])
            ->filters([
                //
            ])
            ->headerActions([
//                Tables\Actions\CreateAction::make()
//                    ->label("Tarjima qo'shish"),
            ])
            ->actions([
//                Tables\Actions\EditAction::make()
//                    ->label("Tahrirlash"),
//                Tables\Actions\DeleteAction::make()
//                    ->label("O'chirish"),
            ])
            ->bulkActions([
//                Tables\Actions\BulkActionGroup::make([
////                    Tables\Actions\DeleteBulkAction::make()
////                        ->label("Tanlanganlarni o'chirish"),
//                ]),
            ]);
    }
}
