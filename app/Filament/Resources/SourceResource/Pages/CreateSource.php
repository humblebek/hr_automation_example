<?php

namespace App\Filament\Resources\SourceResource\Pages;

use App\Filament\Resources\SourceResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateSource extends CreateRecord
{
    protected static string $resource = SourceResource::class;

    public function getTitle(): string
    {
        return 'Yangi link qo‘shish';
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
                ->url(SourceResource::getUrl())
        ];
    }
}
