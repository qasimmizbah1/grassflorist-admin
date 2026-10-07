<?php

namespace App\Filament\Resources\DeliverySlotResource\Pages;

use App\Filament\Resources\DeliverySlotResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeliverySlot extends EditRecord
{
    protected static string $resource = DeliverySlotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
