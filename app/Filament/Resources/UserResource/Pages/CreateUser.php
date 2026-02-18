<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return 'Yangi foydalanuvchi qo‘shish';
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
                ->url(UserResource::getUrl())
        ];
    }
}
