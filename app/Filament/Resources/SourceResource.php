<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SourceResource\Pages;
use App\Filament\Resources\SourceResource\RelationManagers;
use App\Models\Source;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Webbingbrasil\FilamentCopyActions\Tables\CopyableTextColumn;

class SourceResource extends Resource
{
    protected static ?string $model = Source::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationLabel = "Linklar";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\TextInput::make('name')
                        ->label("Platforma yoki kanal nomi")
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('code')
                        ->label("Kodi")
                        ->required()
                        ->maxLength(255)
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $set('link', "https://t.me/@test_teacherly_bot?start={$state}");
                        }),

                    Forms\Components\Section::make([
                        Forms\Components\TextInput::make('link')
                            ->label("Havola")
                            ->required()
                            ->maxLength(255)
                            ->dehydrated(), // still save to DB
                    ])
                ])->columns(2)
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label("Platforma yoki kanal nomi")
                    ->searchable(),
                CopyableTextColumn::make('link')
                    ->label("Havola")
                    ->copyMessage('Havola nusxalandi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSources::route('/'),
            'create' => Pages\CreateSource::route('/create'),
            'edit' => Pages\EditSource::route('/{record}/edit'),
        ];
    }
}
