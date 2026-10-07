<?php
// app/Filament/Resources/HomePageResource.php

namespace App\Filament\Resources;

use App\Filament\Resources\HomePageResource\Pages;
use App\Filament\Resources\HomePageResource\RelationManagers;
use App\Models\HomePage;
use App\Models\Product;
use App\Models\Category;
use App\Models\Production;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;


class HomePageResource extends Resource
{
    protected static ?string $model = HomePage::class;
    protected static ?string $navigationGroup = 'Pages';
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $modelLabel = 'Home Page';
    protected static ?string $navigationLabel = 'Home Page';
    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('home_page') ?? false;
    }

    public static function getRecordTitle(?\Illuminate\Database\Eloquent\Model $record): ?string
    {
        return 'Home Page';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Update Information')
                    ->schema([
                        Forms\Components\Placeholder::make('last_updated_at')
                            ->label('Last Updated')
                            ->content(function (?HomePage $record) {
                                return $record?->updated_at
                                    ? $record->updated_at->format('d M Y, h:i A')
                                    : 'Not updated yet';
                            }),

                        Forms\Components\Placeholder::make('updated_by')
                            ->label('Updated By')
                            ->content(function (?HomePage $record) {
                                return $record?->updatedBy?->name ?? 'Not available';
                            }),
                    ])
                    ->columns(2)
                    ->visible(fn (string $operation) => $operation === 'edit'),

                // 1. Desktop Sliders
                Forms\Components\Section::make('Desktop Sliders & Banners')
                    ->description('Main banners displayed on desktop screens')
                    ->schema([
                        Forms\Components\Repeater::make('slider_section')
                            ->label('Desktop Sliders')
                            ->schema([
                                Forms\Components\FileUpload::make('slider_image')
                                    ->image()
                                    ->disk('public')
                                    ->directory('home-page/banner'),

                                Forms\Components\TextInput::make('slider_url')
                                    ->label('Redirect URL / Link'),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                // 2. Mobile Sliders
                Forms\Components\Section::make('Mobile Sliders')
                    ->description('Banners optimized for mobile phones')
                    ->schema([
                        Forms\Components\Repeater::make('mslider_section')
                            ->label('Mobile Sliders')
                            ->schema([
                                Forms\Components\FileUpload::make('mslider_image')
                                    ->label('Mobile Image')
                                    ->image()
                                    ->disk('public')
                                    ->directory('home-page/banner'),

                                Forms\Components\TextInput::make('mslider_url')
                                    ->label('Redirect URL / Link'),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                // 3. Popular Categories Section (Titles + Categories together)
                Forms\Components\Section::make('Popular Categories Section')
                    ->description('Configure titles and choose the categories to display in this section')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('popular_title.en')
                                    ->label('Section Title (English)')
                                    ->default('Popular Categories')
                                    ->placeholder('e.g. Popular Categories'),
                                Forms\Components\TextInput::make('popular_title.ar')
                                    ->label('Section Title (Arabic - عربي)')
                                    ->extraInputAttributes(['dir' => 'rtl'])
                                    ->default('التصنيفات الأكثر طلباً')
                                    ->placeholder('مثال: التصنيفات الأكثر طلباً'),
                                Forms\Components\TextInput::make('popular_subtitle.en')
                                    ->label('Subtitle (English)')
                                    ->default('Explore fresh bouquets by category'),
                                Forms\Components\TextInput::make('popular_subtitle.ar')
                                    ->label('Subtitle (Arabic - عربي)')
                                    ->extraInputAttributes(['dir' => 'rtl'])
                                    ->default('استكشف باقات الزهور حسب التصنيف'),
                            ]),

                        Forms\Components\Repeater::make('popular_category')
                            ->label('Selected Categories (Drag & Drop to set display order)')
                            ->simple(
                                Forms\Components\Select::make('category_id')
                                    ->label('Category')
                                    ->options(function () {
                                        return Category::all()->mapWithKeys(function ($cat) {
                                            $en = $cat->getTranslation('name', 'en') ?: $cat->slug;
                                            $ar = $cat->getTranslation('name', 'ar') ?: '';
                                            $label = ($en && $ar && $en !== $ar) ? "{$en} ({$ar})" : ($en ?: $ar);
                                            return [(string) $cat->id => $label];
                                        })->toArray();
                                    })
                                    ->searchable()
                                    ->required()
                            )
                            ->addActionLabel('+ Add Category to Section')
                            ->reorderable()
                            ->columnSpanFull(),
                    ]),

                // 4. Best Sellers Section (Titles + Products together)
                Forms\Components\Section::make('Best Sellers Section')
                    ->description('Configure titles and select the best seller products')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('best_sellers_title.en')
                                    ->label('Section Title (English)')
                                    ->default('Best Sellers'),
                                Forms\Components\TextInput::make('best_sellers_title.ar')
                                    ->label('Section Title (Arabic - عربي)')
                                    ->extraInputAttributes(['dir' => 'rtl'])
                                    ->default('الأفضل مبيعاً'),
                                Forms\Components\TextInput::make('best_sellers_subtitle.en')
                                    ->label('Subtitle (English)')
                                    ->default('Our most loved flower arrangements'),
                                Forms\Components\TextInput::make('best_sellers_subtitle.ar')
                                    ->label('Subtitle (Arabic - عربي)')
                                    ->extraInputAttributes(['dir' => 'rtl'])
                                    ->default('أجمل باقات الورد والهدايا المختارة بعناية'),
                            ]),

                        Forms\Components\Repeater::make('best_sellers')
                            ->label('Selected Best Seller Products (Drag & Drop to set display order)')
                            ->simple(
                                Forms\Components\Select::make('product_id')
                                    ->label('Product')
                                    ->options(function () {
                                        return Product::visibleToCustomers()->get()->mapWithKeys(function ($prod) {
                                            $en = $prod->getTranslation('name', 'en') ?: $prod->sku;
                                            $ar = $prod->getTranslation('name', 'ar') ?: '';
                                            $label = ($en && $ar && $en !== $ar) ? "{$en} ({$ar})" : ($en ?: $ar);
                                            if ($prod->sku) {
                                                $label = "[{$prod->sku}] {$label}";
                                            }
                                            return [(string) $prod->id => $label];
                                        })->toArray();
                                    })
                                    ->searchable()
                                    ->required()
                            )
                            ->addActionLabel('+ Add Product to Best Sellers')
                            ->reorderable()
                            ->columnSpanFull(),
                    ]),

                // 5. Featured Flowers Section Titles
                Forms\Components\Section::make('Featured Flowers & New Arrivals Section')
                    ->description('Section headers for featured and latest flower collections')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('featured_products_title.en')
                                    ->label('Featured Section Title (English)')
                                    ->default('Featured Flowers'),
                                Forms\Components\TextInput::make('featured_products_title.ar')
                                    ->label('Featured Section Title (Arabic - عربي)')
                                    ->extraInputAttributes(['dir' => 'rtl'])
                                    ->default('زهور مميزة'),
                                Forms\Components\TextInput::make('latest_products_title.en')
                                    ->label('New Arrivals Title (English)')
                                    ->default('New Arrivals'),
                                Forms\Components\TextInput::make('latest_products_title.ar')
                                    ->label('New Arrivals Title (Arabic - عربي)')
                                    ->extraInputAttributes(['dir' => 'rtl'])
                                    ->default('وصل حديثاً'),
                            ]),
                    ])
                    ->collapsible(),

                // 6. Promotional Banner Section
                Forms\Components\Section::make('Promotional Banner Section')
                    ->description('Configure promo banner image, links, and marketing texts')
                    ->schema([
                        Forms\Components\FileUpload::make('banner_images')
                            ->label('Banner Image')
                            ->image()
                            ->disk('public')
                            ->directory('home-page/banner'),

                        Forms\Components\TextInput::make('banner_button_url')
                            ->label('Button Redirect URL')
                            ->placeholder('e.g. /category/all-flowers'),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('banner_button_title.en')
                                    ->label('Button Text (English)')
                                    ->default('Order Now'),
                                Forms\Components\TextInput::make('banner_button_title.ar')
                                    ->label('Button Text (Arabic - عربي)')
                                    ->extraInputAttributes(['dir' => 'rtl'])
                                    ->default('اطلب الآن'),
                                Forms\Components\Textarea::make('banner_description.en')
                                    ->label('Banner Promo Text (English)')
                                    ->rows(2),
                                Forms\Components\Textarea::make('banner_description.ar')
                                    ->label('Banner Promo Text (Arabic - عربي)')
                                    ->extraInputAttributes(['dir' => 'rtl'])
                                    ->rows(2),
                            ]),
                    ])
                    ->collapsible(),

                // 7. SEO Metadata Section
                Forms\Components\Section::make('Home Page SEO Metadata')
                    ->description('Search engine optimization title, description, and keywords')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('meta_tag_title.en')
                                    ->label('Meta Title (English)'),
                                Forms\Components\TextInput::make('meta_tag_title.ar')
                                    ->label('Meta Title (Arabic - عربي)')
                                    ->extraInputAttributes(['dir' => 'rtl']),
                                Forms\Components\TextInput::make('meta_tag_keywords.en')
                                    ->label('Meta Keywords (English)'),
                                Forms\Components\TextInput::make('meta_tag_keywords.ar')
                                    ->label('Meta Keywords (Arabic - عربي)')
                                    ->extraInputAttributes(['dir' => 'rtl']),
                                Forms\Components\Textarea::make('meta_tag_description.en')
                                    ->label('Meta Description (English)')
                                    ->rows(2),
                                Forms\Components\Textarea::make('meta_tag_description.ar')
                                    ->label('Meta Description (Arabic - عربي)')
                                    ->extraInputAttributes(['dir' => 'rtl'])
                                    ->rows(2),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                 Tables\Columns\TextColumn::make('page_title')
                    ->label('Page')
                    ->weight('bold')
                    ,
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHomePages::route('/'),
            'create' => Pages\CreateHomePage::route('/create'),
            'edit' => Pages\EditHomePage::route('/{record}/edit'),
        ];
    }

    public static function getNavigationUrl(): string
{
    $recordId = \App\Models\HomePage::query()->first()?->id;

    return $recordId
        ? static::getUrl('edit', ['record' => $recordId])
        : static::getUrl('index'); // fallback
}

}