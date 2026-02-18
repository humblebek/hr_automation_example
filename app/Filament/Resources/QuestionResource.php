<?php

namespace App\Filament\Resources;

use App\Enum\LangCode;
use App\Enum\QuestionFile;
use App\Enum\QuestionType;
use App\Filament\Resources\QuestionResource\Pages;
use App\Filament\Resources\QuestionResource\RelationManagers;
use App\Models\Occupation;
use App\Models\OccupationTranslation;
use App\Models\Question;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;


class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationLabel = "Savollar";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\Select::make('occupation_id')
                        ->label('Kasb')
                        ->options(
                            OccupationTranslation::query()
                                ->where('lang_code', LangCode::UZ->value)
                                ->pluck('title', 'occupation_id')
                        )
                        ->searchable()
                        ->required(fn ($get) => $get('question_type') != \App\Enum\QuestionType::OPTIONAL->value),
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\Select::make('question_type')
                                ->label("Savol turi")
                                ->required()
                                ->options(QuestionType::getList()),
                            Forms\Components\TextInput::make('order')
                                ->label("Tartib")
                                ->required()
                                ->numeric(),
                        ])->columns(2),


                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('occupation_id')
                    ->label('Vakansiya')
                    ->getStateUsing(function ($record) {
                        $occupation = $record->occupation;

                        if (!$occupation) {
                            return 'Umumiy savol';
                        }

                        $translation = $occupation->occupationTranslations()
                            ->where('lang_code', \App\Enum\LangCode::UZ->value)
                            ->first();

                        return $translation?->title ?? 'Umumiy savol';
                    })
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('question_text')
                    ->label('Savol matni')
                    ->getStateUsing(function ($record) {
                        $translation = $record->questionTranslations()
                            ->where('lang_code', \App\Enum\LangCode::UZ->value)
                            ->first();

                        return $translation?->text;
                    })
                    ->wrap()
                    ->html()
                    ->sortable()
                    ->searchable(false),

                Tables\Columns\TextColumn::make('order')
                    ->label("Tartibi")
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
                Tables\Filters\Filter::make('question_text')
                    ->label('Savol matni')
                    ->form([
                        Forms\Components\TextInput::make('text')
                            ->label('Matn bilan qidirish')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->whereHas('questionTranslations', function ($q) use ($data) {
                            if (!empty($data['text'])) {
                                $q->where('lang_code', \App\Enum\LangCode::UZ->value)
                                    ->where('text', 'ILIKE', "%{$data['text']}%");
                            }
                        });
                    }),
                SelectFilter::make('occupation_id')
                    ->label("Bo'sh ish o'rni")
                    ->options(
                        Occupation::with(['occupationTranslations' => function ($query) {
                            $query->where('lang_code', LangCode::UZ->value);
                        }])
                            ->get()
                            ->mapWithKeys(function ($occupation) {
                                $translation = $occupation->occupationTranslations->first();
                                return [$occupation->id => $translation?->title ?? 'Noma’lum'];
                            })
                            ->toArray()
                    )
                    ->searchable()
                    ->getSearchResultsUsing(function (string $search) {
                        return Occupation::whereHas('occupationTranslations', function ($query) use ($search) {
                            $query->where('title', 'ilike', "%{$search}%")
                                ->where('lang_code', LangCode::UZ->value);
                        })
                            ->limit(10)
                            ->get()
                            ->mapWithKeys(function ($occupation) {
                                $translation = $occupation->occupationTranslations->first();
                                return [$occupation->id => $translation?->title ?? 'Noma’lum'];
                            })
                            ->toArray();
                    })
                    ->getOptionLabelUsing(function ($value) {
                        $occupation = Occupation::with(['occupationTranslations' => function ($query) {
                            $query->where('lang_code', LangCode::UZ->value);
                        }])->find($value);

                        return $occupation?->occupationTranslations->first()?->title ?? 'Noma’lum';
                    }),
                Tables\Filters\SelectFilter::make('question_type')
                    ->options(QuestionType::getList())
                    ->label('Savol turi'),
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
            RelationManagers\QuestionTranslationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestion::route('/create'),
            'edit' => Pages\EditQuestion::route('/{record}/edit'),
        ];
    }
}
