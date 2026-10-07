<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentGatewayResource\Pages;
use App\Models\PaymentGateway;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentGatewayResource extends Resource
{
    protected static ?string $model = PaymentGateway::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Payment Gateways';
    protected static ?string $modelLabel = 'Payment Gateway';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('payment_gateways') ?? true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Gateway Identity & Status')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('code')
                                    ->label('System Code')
                                    ->disabled()
                                    ->required(),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Active on Checkout')
                                    ->helperText('Enable or disable this payment method for customers.')
                                    ->default(true),

                                Forms\Components\Toggle::make('is_sandbox')
                                    ->label('Test / Sandbox Mode')
                                    ->helperText('When enabled, test cards & test API will be used.')
                                    ->default(false),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name_en')
                                    ->label('Display Name (English)')
                                    ->required(),

                                Forms\Components\TextInput::make('name_ar')
                                    ->label('Display Name (Arabic)')
                                    ->required(),

                                Forms\Components\Textarea::make('description_en')
                                    ->label('Description (English)')
                                    ->rows(2),

                                Forms\Components\Textarea::make('description_ar')
                                    ->label('Description (Arabic)')
                                    ->rows(2),
                            ]),
                    ]),

                Forms\Components\Section::make('API Keys & Credentials')
                    ->description('Enter live or sandbox credentials for this payment gateway.')
                    ->schema([
                        // Dynamic credentials based on gateway code
                        Forms\Components\KeyValue::make('credentials')
                            ->label('Gateway Credentials (Key-Value)')
                            ->helperText('e.g. access_token, entity_id_mada, entity_id_applepay, entity_id_visa_master, public_key, secret_key')
                            ->keyLabel('Credential Key')
                            ->valueLabel('Secret Value / ID')
                            ->reorderable(),
                    ]),

                Forms\Components\Section::make('Order Limits & Surcharges')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('min_order_amount')
                                    ->label('Minimum Order Amount (SAR)')
                                    ->numeric()
                                    ->prefix('SAR'),

                                Forms\Components\TextInput::make('max_order_amount')
                                    ->label('Maximum Order Amount (SAR)')
                                    ->numeric()
                                    ->prefix('SAR'),

                                Forms\Components\TextInput::make('extra_fee')
                                    ->label('Extra Handling / COD Fee (SAR)')
                                    ->numeric()
                                    ->prefix('SAR')
                                    ->default(0.00),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_en')
                    ->label('Gateway Name')
                    ->searchable()
                    ->description(fn (PaymentGateway $record) => $record->name_ar),

                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_sandbox')
                    ->label('Sandbox')
                    ->boolean()
                    ->trueIcon('heroicon-o-beaker')
                    ->falseIcon('heroicon-o-check-badge')
                    ->trueColor('warning')
                    ->falseColor('success'),

                Tables\Columns\TextColumn::make('extra_fee')
                    ->label('Extra Fee')
                    ->money('SAR'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('d M Y, h:i A')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentGateways::route('/'),
            'edit' => Pages\EditPaymentGateway::route('/{record}/edit'),
        ];
    }
}
