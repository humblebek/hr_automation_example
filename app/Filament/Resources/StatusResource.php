<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StatusResource\Pages;
use App\Filament\Resources\StatusResource\RelationManagers;
use App\Models\Status;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StatusResource extends Resource
{
    protected static ?string $model = Status::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = "Statuslar";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\TextInput::make('code')
                        ->required()
                        ->disabledOn('edit'),
                    Forms\Components\Select::make('color')
                        ->label('Rang (badge)')
                        ->required()
                        ->options([
                            'primary' => '🟦 Primary',
                            'success' => '🟩 Success',
                            'warning' => '🟨 Warning',
                            'danger' => '🟥 Danger',
                            'info' => '🟪 Info',
                            'gray' => '⬜ Gray',
                        ])
                        ->allowHtml()
                        ->default('gray')

                ])->columns(2)

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('status_name')
                    ->label('Nomi')
                    ->getStateUsing(function ($record) {
                        $translation = $record->statusTranslations
                            ->where('lang_code', \App\Enum\LangCode::UZ->value)
                            ->first();
                        return $translation?->name;
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                    ->numeric()
                    ->sortable(),
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
            RelationManagers\StatusTranslationsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStatuses::route('/'),
            'create' => Pages\CreateStatus::route('/create'),
            'edit' => Pages\EditStatus::route('/{record}/edit'),
        ];
    }
}
