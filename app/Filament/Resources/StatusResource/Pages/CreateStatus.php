<?php

namespace App\Filament\Resources\StatusResource\Pages;

use App\Filament\Resources\StatusResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateStatus extends CreateRecord
{
    protected static string $resource = StatusResource::class;

    public function getTitle(): string
    {
        return 'Yangi status qo‘shish';
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
                ->url(StatusResource::getUrl())
        ];
    }
}
