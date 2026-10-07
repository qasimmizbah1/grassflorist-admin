<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CmsCategoryResource\Pages;
use App\Models\CmsCategory;
use App\Services\StoreImportService;
use Filament\Forms;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CmsCategoryResource extends Resource
{
    protected static ?string $model = CmsCategory::class;

    protected static ?string $navigationIcon = 'heroicon-c-at-symbol';
    protected static ?string $navigationGroup = 'Blog';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Blog Category';
    protected static ?string $pluralModelLabel = 'Blog Categories';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('cms_categories') ?? false;
    }

    public static function getRecordTitle(?\Illuminate\Database\Eloquent\Model $record): ?string
    {
        if (! $record) return null;
        return format_translatable($record->name, 'en')
            ?: (format_translatable($record->name, 'ar') ?: ($record->slug ?: ('Category #' . $record->id)));
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Update Information')
                    ->schema([
                        Forms\Components\Placeholder::make('last_updated_at')
                            ->label('Last Updated')
                            ->content(function (?CmsCategory $record) {
                                return $record?->updated_at
                                    ? $record->updated_at->format('d M Y, h:i A')
                                    : 'Not updated yet';
                            }),

                        Forms\Components\Placeholder::make('updated_by')
                            ->label('Updated By')
                            ->content(function (?CmsCategory $record) {
                                return $record?->updatedBy?->name ?? 'Not available';
                            }),
                    ])
                    ->columns(2)
                    ->visible(fn (string $operation) => $operation === 'edit'),

                Tabs::make('Category Translations & Details')
                    ->tabs([
                        Tabs\Tab::make('English (EN)')
                            ->icon('heroicon-o-language')
                            ->schema([
                                TextInput::make('name.en')
                                    ->label('Category Name (English)')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $operation, $state, Forms\Set $set, Forms\Get $get) {
                                        if ($operation === 'create' && empty($get('slug'))) {
                                            $set('slug', Str::slug($state));
                                        }
                                    }),

                                TextInput::make('slug')
                                    ->label('Slug (English)')
                                    ->required()
                                    ->unique(ignoreRecord: true),

                                Textarea::make('content.en')
                                    ->label('Description (English)')
                                    ->rows(3),

                                Forms\Components\Section::make('SEO Metadata (English)')
                                    ->schema([
                                        TextInput::make('meta_tag_title.en')
                                            ->label('Meta Title (English)'),
                                        TextInput::make('meta_tag_keywords.en')
                                            ->label('Meta Keywords (English)'),
                                        Textarea::make('meta_tag_description.en')
                                            ->label('Meta Description (English)')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2)
                                    ->collapsible()
                                    ->collapsed(),
                            ]),

                        Tabs\Tab::make('Arabic (عربي)')
                            ->icon('heroicon-o-globe-alt')
                            ->schema([
                                TextInput::make('name.ar')
                                    ->label('Category Name (Arabic)')
                                    ->extraInputAttributes(['dir' => 'rtl'])
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $operation, $state, Forms\Set $set, Forms\Get $get) {
                                        if ($operation === 'create' && empty($get('slug_ar'))) {
                                            $clean = str_replace(' ', '-', trim($state));
                                            $set('slug_ar', $clean);
                                        }
                                    }),

                                TextInput::make('slug_ar')
                                    ->label('Slug (Arabic)')
                                    ->extraInputAttributes(['dir' => 'rtl']),

                                Textarea::make('content.ar')
                                    ->label('Description (Arabic)')
                                    ->extraInputAttributes(['dir' => 'rtl'])
                                    ->rows(3),

                                Forms\Components\Section::make('SEO Metadata (Arabic)')
                                    ->schema([
                                        TextInput::make('meta_tag_title.ar')
                                            ->label('Meta Title (Arabic)')
                                            ->extraInputAttributes(['dir' => 'rtl']),
                                        TextInput::make('meta_tag_keywords.ar')
                                            ->label('Meta Keywords (Arabic)')
                                            ->extraInputAttributes(['dir' => 'rtl']),
                                        Textarea::make('meta_tag_description.ar')
                                            ->label('Meta Description (Arabic)')
                                            ->extraInputAttributes(['dir' => 'rtl'])
                                            ->rows(2)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2)
                                    ->collapsible()
                                    ->collapsed(),
                            ]),
                    ])
                    ->columnSpanFull(),

                Forms\Components\Section::make('Status & Integration')
                    ->schema([
                        TextInput::make('source_id')
                            ->label('WordPress ID')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn (?CmsCategory $record) => filled($record?->source_id)),

                        Toggle::make('is_active')
                            ->label('Is Active')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Category Name')
                    ->formatStateUsing(function ($state, CmsCategory $record) {
                        $en = $record->getTranslation('name', 'en') ?: '';
                        $ar = $record->getTranslation('name', 'ar') ?: '';
                        if ($en && $ar && $en !== $ar) {
                            return "{$ar} ({$en})";
                        }
                        return $ar ?: ($en ?: '-');
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug (EN)')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('slug_ar')
                    ->label('Slug (AR)')
                    ->badge()
                    ->color('gray')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('posts_count')
                    ->counts('posts')
                    ->label('Posts')
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make(),
                Action::make('sync_wp_blog_categories')
                    ->label('Sync from WordPress')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Sync Blog Categories from WordPress')
                    ->modalDescription('Do you want to fetch and update blog categories directly from grassflorist.com?')
                    ->action(function (StoreImportService $importService) {
                        try {
                            $res = $importService->importBlogCategories();
                            Notification::make()
                                ->title('Blog Categories Synced!')
                                ->body("Successfully imported/updated {$res['imported']} blog categories.")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Sync Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCmsCategories::route('/'),
            'create' => Pages\CreateCmsCategory::route('/create'),
            'edit' => Pages\EditCmsCategory::route('/{record}/edit'),
        ];
    }
}
