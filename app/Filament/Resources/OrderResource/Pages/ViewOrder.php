<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('printGiftCard')
                ->label('Print Gift Card')
                ->icon('heroicon-o-gift')
                ->color('success')
                ->url(fn () => route('orders.gift_card', $this->record->id))
                ->openUrlInNewTab(),

            Actions\Action::make('printTaxInvoice')
                ->label('Print Tax Invoice')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->url(fn () => route('orders.tax_invoice', $this->record->id))
                ->openUrlInNewTab(),

            Actions\EditAction::make(),
        ];
    }
}
