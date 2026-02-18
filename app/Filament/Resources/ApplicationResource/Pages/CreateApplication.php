<?php

namespace App\Filament\Resources\ApplicationResource\Pages;

use App\Filament\Resources\ApplicationResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateApplication extends CreateRecord
{
    protected static string $resource = ApplicationResource::class;

//    public function getTitle(): string
//    {
//        return 'Yangi applikant qo‘shish';
//    }
//
//    protected function getFormActions(): array
//    {
//        return [
//            Action::make('create')
//                ->label("Qo'shish")
//                ->color('success')
//                ->submit('create'),
//
//            Action::make('cancel')
//                ->label('Bekor qilish')
//                ->color('danger')
//                ->url(ApplicationResource::getUrl())
//        ];
//    }
}
