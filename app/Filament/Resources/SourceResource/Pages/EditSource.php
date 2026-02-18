<?php

namespace App\Filament\Resources\SourceResource\Pages;

use App\Filament\Resources\SourceResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditSource extends EditRecord
{
    protected static string $resource = SourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label("O'chirish"),
        ];
    }

    public function getTitle(): string
    {
        return 'Linkni tahrirlash';
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Saqlash')
                ->color('success')
                ->submit('save'),

            Action::make('cancel')
                ->label('Bekor qilish')
                ->color('primary')
                ->url(SourceResource::getUrl())
        ];
    }
}
