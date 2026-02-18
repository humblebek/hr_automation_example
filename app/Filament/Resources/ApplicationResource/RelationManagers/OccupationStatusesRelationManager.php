<?php

namespace App\Filament\Resources\ApplicationResource\RelationManagers;

use App\Enum\LangCode;
use App\Models\StatusTranslation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;


class OccupationStatusesRelationManager extends RelationManager
{
    protected static string $relationship = 'occupationStatuses';

    protected static ?string $title = "Arizalari";

    public function form(Form $form): Form
    {
        $lang = LangCode::UZ->value;

        return $form->schema([
            Forms\Components\Select::make('occupation_id')
                ->label('Kasb')
                ->required()
                ->disabled()
                ->options(function () use ($lang) {
                    return \App\Models\Occupation::with(['occupationTranslations' => fn($q) => $q->where('lang_code', $lang)])
                        ->get()
                        ->mapWithKeys(fn($occ) => [
                            $occ->id => $occ->occupationTranslations->first()?->title ?? '—',
                        ])
                        ->toArray();
                }),


            Forms\Components\Select::make('status_id')
                ->label('Status')
                ->required()
                ->options(
                    StatusTranslation::query()
                        ->where('lang_code', $lang)
                        ->pluck('name', 'status_id')
                        ->toArray()
                ),
        ]);
    }

    public function table(Table $table): Table
    {
        $lang = LangCode::UZ->value;
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('occupation.occupationTranslations.title')
                    ->label('Kasb')
                    ->formatStateUsing(fn($record) => $record->occupation?->occupationTranslations()
                        ->where('lang_code', $lang)
                        ->value('title') ?? '—'
                    )
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('status.statusTranslations.name')
                    ->label('Status')
                    ->formatStateUsing(fn($record) => $record->status?->statusTranslations()
                        ->where('lang_code', $lang)
                        ->value('name') ?? '—'
                    )
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label("Tahrirlash")
                    ->modalHeading('Kasb holatini tahrirlash')
                    ->modalSubmitActionLabel('Saqlash')
                    ->modalCancelActionLabel('Bekor qilish')
                    ->icon('heroicon-o-pencil-square'),
            ]);
    }
}
