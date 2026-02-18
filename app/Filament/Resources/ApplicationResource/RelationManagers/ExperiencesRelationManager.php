<?php

namespace App\Filament\Resources\ApplicationResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExperiencesRelationManager extends RelationManager
{
    protected static string $relationship = 'experiences';

    protected static ?string $title = "Tajriba";

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('where_work')
                    ->label("Ishlagan joyi")
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('what_job')
                    ->label("Lavozimi")
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('duration')
                    ->label("Ishlagan muddati")
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('where_work')
                    ->label("Ishlagan joyi"),
                Tables\Columns\TextColumn::make('what_job')
                    ->label("Lavozimi"),
                Tables\Columns\TextColumn::make('duration')
                    ->label("Ishlagan muddati"),
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
