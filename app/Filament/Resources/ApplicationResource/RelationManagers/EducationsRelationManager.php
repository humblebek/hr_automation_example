<?php

namespace App\Filament\Resources\ApplicationResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EducationsRelationManager extends RelationManager
{
    protected static string $relationship = 'educations';

    protected static ?string $title = "Ta'lim";

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\TextInput::make('study_level')
                        ->label("Darajasi")
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('direction')
                        ->label("Yo'nalishi")
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('where_study')
                        ->label("O'quv yurti")
                        ->required()
                        ->maxLength(255),
                ])

            ]);
    }

    public function table(Table $table): Table
    {
        return $table

            ->columns([
                Tables\Columns\TextColumn::make('study_level')
                    ->label("Darajasi"),
                Tables\Columns\TextColumn::make('direction')
                    ->label("Yo'nalishi"),
                Tables\Columns\TextColumn::make('where_study')
                    ->label("O'quv yurti"),
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
//                    Tables\Actions\DeleteBulkAction::make()
//                        ->label("Tanlanganlarni o'chirish"),
//                ]),
            ]);
    }
}
