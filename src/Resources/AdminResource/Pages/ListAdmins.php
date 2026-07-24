<?php

namespace OoriyaP\FilamentSimpleOtp\Resources\AdminResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use OoriyaP\FilamentSimpleOtp\Resources\AdminResource;

class ListAdmins extends ListRecords
{
    protected static string $resource = AdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
