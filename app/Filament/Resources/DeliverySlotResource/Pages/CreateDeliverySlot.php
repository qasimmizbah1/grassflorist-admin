<?php

namespace App\Filament\Resources\DeliverySlotResource\Pages;

use App\Filament\Resources\DeliverySlotResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDeliverySlot extends CreateRecord
{
    protected static string $resource = DeliverySlotResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
