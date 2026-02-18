<?php

namespace App\Filament\Resources;

use App\Enum\LangCode;
use App\Enum\Status;
use App\Filament\Resources\OccupationResource\Pages;
use App\Filament\Resources\OccupationResource\RelationManagers;
use App\Models\Department;
use App\Models\DepartmentTranslation;
use App\Models\Occupation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;


class OccupationResource extends Resource
{
    protected static ?string $model = Occupation::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationLabel = "Bo'sh ish o'rinlari";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\Select::make('department_id')
                                ->label('Bo‘lim')
                                ->options(
                                    \App\Models\Department::query()
                                        ->where('status', \App\Enum\Status::ACTIVE->value)
                                        ->whereHas('departmentTranslations', fn($q) => $q->where('lang_code', \App\Enum\LangCode::UZ->value)
                                        )
                                        ->with(['departmentTranslations' => fn($q) => $q->where('lang_code', \App\Enum\LangCode::UZ->value)
                                        ])
                                        ->get()
                                        ->pluck('departmentTranslations.0.name', 'id')
                                )
                                ->searchable()
                                ->required(),

                            Forms\Components\Select::make('status')
                                ->required()
                                ->options(Status::getList()),
                        ])->columns(2),

                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\TextInput::make('hh_link')
                                ->label("Vakansiya qo'yilgan platforma havolasi (agar mavjud bo'lsa)"),
                        ]),
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\FileUpload::make('photo')
                                ->label("Rasm")
                                ->directory('occupation')
                                ->image()
                                ->deletable(true)
                                ->downloadable(true)
                                ->required(false),
                        ])

                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('department_id')
                    ->label('Bo‘lim')
                    ->getStateUsing(function ($record) {
                        $translation = $record->department
                            ? $record->department->departmentTranslations()
                                ->where('lang_code', \App\Enum\LangCode::UZ->value)
                                ->first()
                            : null;

                        return $translation?->name ?? 'Noma’lum';
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('occupation_title')
                    ->label('Nomi')
                    ->getStateUsing(function ($record) {
                        $translation = $record->occupationTranslations
                            ->where('lang_code', \App\Enum\LangCode::UZ)
                            ->first();

                        return $translation?->title ?? '';
                    })
                    ->sortable()
                    ->searchable(false),

                Tables\Columns\TextColumn::make('status')
                    ->formatStateUsing(fn($state) => Status::name($state))
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
                SelectFilter::make('department_id')
                    ->label("Bo'lim")
                    ->options(
                        Department::with(['departmentTranslations' => function ($query) {
                            $query->where('lang_code', LangCode::UZ->value);
                        }])
                            ->get()
                            ->mapWithKeys(function ($department) {
                                $translation = $department->departmentTranslations->first();
                                return [$department->id => $translation?->name ?? 'Noma’lum'];
                            })
                            ->toArray()
                    )
                    ->searchable()
                    ->getSearchResultsUsing(function (string $search) {
                        return Department::whereHas('departmentTranslations', function ($query) use ($search) {
                            $query->where('name', 'ilike', "%{$search}%")
                                ->where('lang_code', LangCode::UZ->value);
                        })
                            ->limit(10)
                            ->get()
                            ->mapWithKeys(function ($department) {
                                $translation = $department->departmentTranslations->first();
                                return [$department->id => $translation?->name ?? 'Noma’lum'];
                            })
                            ->toArray();
                    })
                    ->getOptionLabelUsing(function ($value) {
                        $department = Occupation::with(['departmentTranslations' => function ($query) {
                            $query->where('lang_code', LangCode::UZ->value);
                        }])->find($value);

                        return $department?->departmentTranslations->first()?->name ?? 'Noma’lum';
                    }),
                Tables\Filters\Filter::make('occupation_title')
                    ->label('Lavozim nomi')
                    ->form([
                        Forms\Components\TextInput::make('title')
                            ->label('Nom bilan qidirish')
                            ->placeholder('Masalan: Dizayner')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->whereHas('occupationTranslations', function ($q) use ($data) {
                            if (!empty($data['title'])) {
                                $q->where('lang_code', \App\Enum\LangCode::UZ->value)
                                    ->where('title', 'ILIKE', "%{$data['title']}%");
                            }
                        });
                    }),

                Tables\Filters\SelectFilter::make('status')
                    ->label("Statusi")
                    ->options(Status::getList()),
            ], layout: Tables\Enums\FiltersLayout::AboveContent)
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
            RelationManagers\OccupationTranslationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOccupations::route('/'),
            'create' => Pages\CreateOccupation::route('/create'),
            'edit' => Pages\EditOccupation::route('/{record}/edit'),
        ];
    }
}
