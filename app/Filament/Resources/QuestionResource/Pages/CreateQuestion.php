<?php

namespace App\Filament\Resources\QuestionResource\Pages;


use App\Filament\Resources\QuestionResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateQuestion extends CreateRecord
{
    protected static string $resource = QuestionResource::class;

    public function getTitle(): string
    {
        return 'Yangi savol qo‘shish';
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label("Qo'shish")
                ->color('success')
                ->submit('create'),

            Action::make('cancel')
                ->label('Bekor qilish')
                ->color('danger')
                ->url(QuestionResource::getUrl())
        ];
    }
}
