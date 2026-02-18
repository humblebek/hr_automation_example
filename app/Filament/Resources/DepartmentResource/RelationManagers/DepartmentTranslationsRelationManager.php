<?php

namespace App\Filament\Resources\DepartmentResource\RelationManagers;

use App\Enum\LangCode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DepartmentTranslationsRelationManager extends RelationManager
{
    protected static string $relationship = 'departmentTranslations';
    protected static ?string $title = "Bo'lim tarjimalari";

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
                         Forms\Components\TextInput::make('name')
                             ->label("Nomi")
                             ->required()
                             ->maxLength(255),
                     ])
                 ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('lang_code')
                    ->label("Til kodi")
                    ->formatStateUsing(fn ($state) => LangCode::name($state)),
                Tables\Columns\TextColumn::make('name')
                    ->label("Nomi"),
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
