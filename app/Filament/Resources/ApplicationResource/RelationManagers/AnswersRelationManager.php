<?php

namespace App\Filament\Resources\ApplicationResource\RelationManagers;

use App\Enum\LangCode;
use App\Models\QuestionTranslation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AnswersRelationManager extends RelationManager
{
    protected static string $relationship = 'answers';

    protected static ?string $title = "Savol javoblar";

    public function form(Form $form): Form
    {
        $lang = LangCode::UZ->value;

        return $form->schema([
            Forms\Components\Select::make('question_id')
                ->label('Savol')
                ->options(
                    QuestionTranslation::query()
                        ->where('lang_code', $lang)
                        ->orderBy('question_id')
                        ->pluck('text', 'question_id')
                )
                ->searchable()
                ->required(),

            // Let admins paste JSON or use a nicer UI:
            Forms\Components\KeyValue::make('answer')
                ->label('Javob (JSON)')
                ->keyLabel('Key')
                ->valueLabel('Value')
                ->editableKeys(true)
                ->columnSpanFull()
                ->required(),
        ]);
    }


    public function table(Table $table): Table
    {
        $lang = LangCode::UZ->value;

        return $table
            ->modifyQueryUsing(function ($query) use ($lang) {
                $query->with([
                    'question.questionTranslations' => fn($q) => $q->where('lang_code', $lang),
                    'question.occupation.occupationTranslations' => fn($q) => $q->where('lang_code', $lang),
                ]);
            })
            ->columns([
                Tables\Columns\TextColumn::make('question.occupation.occupationTranslations.title')
                    ->label("Bo'sh ish o'rni")
                    ->getStateUsing(fn($record) =>
                        $record->question?->occupation?->occupationTranslations->first()?->title ?? '—'
                    )
                    ->sortable()
                    ->searchable(),

                // SAVOL
                Tables\Columns\TextColumn::make('question_text')
                    ->label('Savol')
                    ->getStateUsing(fn($record) =>
                        $record->question?->questionTranslations->first()?->text ?? '—'
                    )
                    ->wrap()
                    ->searchable(),

                // JAVOB
                Tables\Columns\TextColumn::make('answer')
                    ->label('Javob')
                    ->formatStateUsing(function ($state) {
                        if (is_string($state)) {
                            $decoded = json_decode($state, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                $state = $decoded;
                            } else {
                                return $state;
                            }
                        }

                        if (!is_array($state)) {
                            return (string) $state;
                        }

                        if (array_key_exists('value', $state)) {
                            return is_array($state['value'])
                                ? implode(', ', array_map(fn($v) => (string) $v, $state['value']))
                                : (string) $state['value'];
                        }

                        if (isset($state['text']))   return (string) $state['text'];
                        if (isset($state['number'])) return (string) $state['number'];

                        return implode(', ', array_map(fn($v) => (string) $v, array_values($state)));
                    })
                    ->wrap()
                    ->limit(120)
                    ->searchable(),
        ])
            ->actions([
//                Tables\Actions\EditAction::make()->label("Tahrirlash"),
//                Tables\Actions\DeleteAction::make()->label("O'chirish"),
            ])
            ->bulkActions([
//                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make()->label("Tanlanganlarni o'chirish"),
//                ]),
            ]);
    }

}
