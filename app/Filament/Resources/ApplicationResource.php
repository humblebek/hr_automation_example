<?php

namespace App\Filament\Resources;

use App\Enum\Gender;
use App\Enum\Lang;
use App\Enum\Natinalities;
use App\Filament\Resources\ApplicationResource\Pages;
use App\Filament\Resources\ApplicationResource\RelationManagers;
use App\Models\Application;
use App\Models\Occupation;
use App\Models\StatusTranslation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = "Kandidatlar";


    public static function getNavigationBadge(): string
    {
        $count = Application::query()
            ->whereHas('status', fn ($q) => $q->where('code', 100))
            ->count();

        return $count ;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                Forms\Components\TextInput::make('full_name')
                    ->label("Ism familiyasi")
                    ->required()
                    ->disabled()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label("Telefon")
                    ->tel()
                    ->disabled()
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('birth_date')
                    ->label("Tug'ilgan sanasi")
                    ->disabled()
                    ->required(),
                    Forms\Components\Select::make('sex')
                        ->label("Jinsi")
                        ->disabled()
                        ->required()
                        ->options(Gender::getList()),
                    Forms\Components\Select::make('region_id')
                        ->label('Viloyat')
                        ->required()
                        ->disabled()
                        ->options(\App\Models\Region::pluck('name', 'id')->toArray()),

                    Forms\Components\Select::make('district_id')
                        ->label('Tuman')
                        ->required()
                        ->disabled()
                        ->options(function (callable $get) {
                            $regionId = $get('region_id');
                            if (!$regionId) return [];
                            return \App\Models\District::where('region_id', $regionId)
                                ->pluck('name', 'id')
                                ->toArray();
                        }),

                    Forms\Components\Select::make('nationality')
                    ->label("Millati")
                    ->required()
                        ->disabled()
                    ->options(Natinalities::getList()),

                    Forms\Components\Select::make('source_id')
                        ->label('Link')
                        ->disabled()
                        ->nullable()
                        ->options(\App\Models\Source::pluck('name', 'id')->toArray()),

                ])->columns(2),
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('status_id')
                            ->label('Statusi')
                            ->required()
                            ->options(function () {
                                $lang = app()->getLocale();
                                return StatusTranslation::where('lang_code', $lang)
                                    ->pluck('name', 'status_id');
                            })
                            ->searchable()
                            ->preload(),
                        ]),
                    Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->label("Rasmi")
                            ->disk('public')
                            ->directory('applications')
                            ->image()
                            ->disabled()
                            ->imageEditor()
                            ->openable()
                            ->downloadable()
                            ->previewable()
                            ->deletable(false)
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label("Ism familiyasi")
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label("Telefon")
                    ->searchable(),
                Tables\Columns\TextColumn::make('answers.question.occupation.occupationTranslations.title')
                    ->label('Vakansiya')
                    ->formatStateUsing(function ($record) {
                        // Each application has many answers; get the first occupation title
                        $occupation = $record->answers
                            ->map(fn ($a) => $a->question?->occupation?->occupationTranslations->first()?->title)
                            ->filter()
                            ->first();

                        return $occupation ?? '—';
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label("Vaqti")
                    ->date('d.m.Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status.statusTranslations.name')
                    ->label('Statusi')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        // Detect current Filament/admin language
                        $lang = app()->getLocale() ?? 'uz';

                        // Fetch the correct translation name
                        return $record->status?->statusTranslations
                            ->where('lang_code', $lang)
                            ->first()?->name
                            ?? '---';
                    })
                    ->color(fn ($record) => $record->status?->color ?? 'gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('occupation')
                    ->label("Bo'sh ish o'rni")
                    ->searchable()
                    ->options(
                        \App\Models\Occupation::with('occupationTranslations')
                            ->get()
                            ->mapWithKeys(fn ($o) => [
                                $o->id => $o->occupationTranslations->first()?->title ?? '—',
                            ])
                    )
                    ->query(function ($query, array $data) {
                        $query->when($data['value'], function ($q, $value) {
                            $q->whereHas('answers.question', fn($q2) => $q2->where('occupation_id', $value));
                        });
                    }),

                Tables\Filters\SelectFilter::make('source_id')
                    ->label("Link")
                    ->relationship('source', 'name'),

                Tables\Filters\SelectFilter::make('status_id')
                    ->label('Statusi')
                    ->options(function () {
                        $lang = \App\Enum\LangCode::UZ->value;
                        return \App\Models\StatusTranslation::where('lang_code', $lang)
                            ->pluck('name', 'status_id');
                    })
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('created_at')
                    ->label('Sana bo‘yicha')
                    ->form([
                        Forms\Components\DatePicker::make('date')->label('Sanani tanlang'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['date'],
                            fn ($q, $date) => $q->whereDate('created_at', $date)
                        );
                    }),
            ], layout: Tables\Enums\FiltersLayout::AboveContent)

            ->actions([
                Tables\Actions\Action::make('resume')
                    ->label('Rezyume (PDF)')
                    ->icon('heroicon-m-document-arrow-down')
                    ->color('success')
                    ->url(fn (Application $record) => route('admin.applications.resume', $record), true),
                Tables\Actions\EditAction::make()
                    ->label("Tahrirlash"),
//                Tables\Actions\DeleteAction::make()
//                    ->label("O'chirish"),
            ])
            ->bulkActions([
//                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make()
//                        ->label("Tanlanganlarni o'chirish"),
//                ]),
            ])->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\EducationsRelationManager::class,
            RelationManagers\ExperiencesRelationManager::class,
            RelationManagers\LanguagesRelationManager::class,
            RelationManagers\AnswersRelationManager::class,
            RelationManagers\OccupationStatusesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApplications::route('/'),
            'create' => Pages\CreateApplication::route('/create'),
            'edit' => Pages\EditApplication::route('/{record}/edit'),
        ];
    }
}
