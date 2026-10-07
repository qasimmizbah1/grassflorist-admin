<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Order;
use Carbon\Carbon;

use Illuminate\Support\Facades\DB;

class StatsOverview extends BaseWidget
{
    public static function canView(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    protected function getStats(): array
    {

        $this->columns = 2; 

        return [
            
            Stat::make('Total Customers', Customer::count())
                ->description('Increase in customers')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),
                
            Stat::make('Total Products', Product::count())
                ->description('Total products in app')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),

             Stat::make('Total Orders', Order::count())
                ->description('Orders')
                ->color('warning')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([9, 3, 4, 7, 6, 2, 5, 9]),

            Stat::make('Last 7 Days Revenue', number_format((float)Order::where('created_at', '>=', Carbon::now()->subDays(7))->sum('total_amount'), 2) . ' SAR')
                ->description('Revenue from last 7 days')
                ->color('rose')
                ->descriptionIcon('heroicon-m-arrow-trending-up'),

            Stat::make('Last Month Revenue', number_format((float)Order::where('created_at', '>=', Carbon::now()->subDays(30))->sum('total_amount'), 2) . ' SAR')
                ->description('Revenue from last 30 days')
                ->color('danger')
                ->descriptionIcon('heroicon-m-arrow-trending-up'),

            Stat::make('Best Selling Product', function () {
                $topItem = \App\Models\OrderItem::select('product_id', 'product_name', DB::raw('SUM(quantity) as total_sold'))
                    ->whereNotNull('product_id')
                    ->groupBy('product_id', 'product_name')
                    ->orderByDesc('total_sold')
                    ->first();

                if (!$topItem) {
                    return 'No sales yet';
                }

                $name = $topItem->product_name;
                if (empty($name) && $topItem->product) {
                    $rawName = $topItem->product->name;
                    $name = is_array($rawName) ? ($rawName['en'] ?? $rawName['ar'] ?? 'Flower Bouquet') : $rawName;
                }

                $name = $name ?: 'Flower Bouquet';
                $sold = (int)($topItem->total_sold ?? 0);
                return "{$name} ({$sold} sold)";
            })
            ->description('By quantity sold')
            ->color('info')
            ->descriptionIcon('heroicon-m-star'),
                



            
        ];
    }
}