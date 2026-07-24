<?php

namespace OoriyaP\FilamentSimpleOtp\Resources\AdminResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use OoriyaP\FilamentSimpleOtp\Resources\AdminResource;

class EditAdmin extends EditRecord
{
    protected static string $resource = AdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
