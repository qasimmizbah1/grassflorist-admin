<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CmsPostResource\Pages;
use App\Models\CmsCategory;
use App\Models\CmsPost;
use App\Services\StoreImportService;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
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

class CmsPostResource extends Resource
{
    protected static ?string $model = CmsPost::class;

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';
    protected static ?string $navigationGroup = 'Blog';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Blog Post';
    protected static ?string $pluralModelLabel = 'Blog Posts';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('cms_posts') ?? false;
    }

    public static function getRecordTitle(?\Illuminate\Database\Eloquent\Model $record): ?string
    {
        if (! $record) return null;
        return format_translatable($record->title, 'en')
            ?: (format_translatable($record->title, 'ar') ?: ($record->slug ?: ('Post #' . $record->id)));
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
                            ->content(function (?CmsPost $record) {
                                return $record?->updated_at
                                    ? $record->updated_at->format('d M Y, h:i A')
                                    : 'Not updated yet';
                            }),

                        Forms\Components\Placeholder::make('updated_by')
                            ->label('Updated By')
                            ->content(function (?CmsPost $record) {
                                return $record?->updatedBy?->name ?? 'Not available';
                            }),
                    ])
                    ->columns(2)
                    ->visible(fn (string $operation) => $operation === 'edit'),

                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\Group::make()->schema([
                        Tabs::make('Post Content & Translations')
                            ->tabs([
                                Tabs\Tab::make('English (EN)')
                                    ->icon('heroicon-o-language')
                                    ->schema([
                                        TextInput::make('title.en')
                                            ->label('Post Title (English)')
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

                                        Textarea::make('short_description.en')
                                            ->label('Short Summary / Excerpt (English)')
                                            ->rows(3),

                                        RichEditor::make('content.en')
                                            ->label('Main Article Content (English)'),

                                        Forms\Components\Section::make('SEO Metadata (English)')
                                            ->schema([
                                                TextInput::make('meta_title.en')
                                                    ->label('Meta Title (English)'),
                                                TextInput::make('meta_keywords.en')
                                                    ->label('Meta Keywords (English)'),
                                                Textarea::make('meta_description.en')
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
                                        TextInput::make('title.ar')
                                            ->label('Post Title (Arabic)')
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

                                        Textarea::make('short_description.ar')
                                            ->label('Short Summary / Excerpt (Arabic)')
                                            ->extraInputAttributes(['dir' => 'rtl'])
                                            ->rows(3),

                                        RichEditor::make('content.ar')
                                            ->label('Main Article Content (Arabic)')
                                            ->extraInputAttributes(['dir' => 'rtl']),

                                        Forms\Components\Section::make('SEO Metadata (Arabic)')
                                            ->schema([
                                                TextInput::make('meta_title.ar')
                                                    ->label('Meta Title (Arabic)')
                                                    ->extraInputAttributes(['dir' => 'rtl']),
                                                TextInput::make('meta_keywords.ar')
                                                    ->label('Meta Keywords (Arabic)')
                                                    ->extraInputAttributes(['dir' => 'rtl']),
                                                Textarea::make('meta_description.ar')
                                                    ->label('Meta Description (Arabic)')
                                                    ->extraInputAttributes(['dir' => 'rtl'])
                                                    ->rows(2)
                                                    ->columnSpanFull(),
                                            ])
                                            ->columns(2)
                                            ->collapsible()
                                            ->collapsed(),
                                    ]),
                            ]),
                    ])->columnSpan(2),

                    Forms\Components\Group::make()->schema([
                        Forms\Components\Section::make('Publishing & Category')
                            ->schema([
                                Select::make('cms_category_id')
                                    ->label('Blog Category')
                                    ->relationship('category', 'name')
                                    ->getOptionLabelFromRecordUsing(fn (CmsCategory $record) => format_translatable($record->name, 'en') ?: (format_translatable($record->name, 'ar') ?: $record->slug))
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),

                                TextInput::make('author')
                                    ->label('Author Name')
                                    ->default('GRASS Florist'),

                                DateTimePicker::make('published_at')
                                    ->label('Published Date')
                                    ->default(now()),

                                TextInput::make('source_id')
                                    ->label('WordPress Post ID')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->visible(fn (?CmsPost $record) => filled($record?->source_id)),

                                Toggle::make('is_active')
                                    ->label('Published / Visible')
                                    ->default(true),
                            ]),

                        Forms\Components\Section::make('Post Media')
                            ->schema([
                                Forms\Components\Placeholder::make('current_image_preview')
                                    ->label('Current Featured Image')
                                    ->content(function (?CmsPost $record) {
                                        if (! $record?->image) {
                                            return new \Illuminate\Support\HtmlString('<span class="text-xs text-gray-500">No image assigned</span>');
                                        }
                                        $src = str_starts_with($record->image, 'http')
                                            ? $record->image
                                            : asset('storage/' . $record->image);
                                        $filename = basename(parse_url($src, PHP_URL_PATH));
                                        return new \Illuminate\Support\HtmlString(
                                            '<div style="overflow: hidden; max-width: 100%; width: 100%; display: flex; align-items: center; gap: 12px; padding: 10px; background-color: #111827; border: 1px solid #374151; border-radius: 8px;">
                                                <img src="' . e($src) . '" style="width: 50px; height: 50px; flex-shrink: 0; object-fit: cover; border-radius: 6px; border: 1px solid #4b5563;" alt="Post Image" />
                                                <div style="min-width: 0; flex: 1 1 0%; overflow: hidden;">
                                                    <span style="display: block; width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 12px; color: #e5e7eb; font-weight: 500;" title="' . e($filename) . '">' . e($filename) . '</span>
                                                    <a href="' . e($src) . '" target="_blank" style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; color: #10b981; margin-top: 4px; text-decoration: none;">Open Full Size ↗</a>
                                                </div>
                                            </div>'
                                        );
                                    })
                                    ->visible(fn (?CmsPost $record) => filled($record?->image)),

                                FileUpload::make('image')
                                    ->label(fn (?CmsPost $record) => filled($record?->image) ? 'Replace Featured Image' : 'Upload Featured Image')
                                    ->image()
                                    ->disk('public')
                                    ->directory('blog/posts'),

                                FileUpload::make('banner_image')
                                    ->label('Optional Header Banner')
                                    ->image()
                                    ->disk('public')
                                    ->directory('blog/banners'),
                            ]),
                    ])->columnSpan(1),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->circular()
                    ->defaultImageUrl('/placeholder-post.png'),

                Tables\Columns\TextColumn::make('title')
                    ->label('Post Title')
                    ->formatStateUsing(function ($state, CmsPost $record) {
                        $en = $record->getTranslation('title', 'en') ?: '';
                        $ar = $record->getTranslation('title', 'ar') ?: '';
                        if ($en && $ar && $en !== $ar) {
                            return "{$ar} ({$en})";
                        }
                        return $ar ?: ($en ?: '-');
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->formatStateUsing(function ($state, ?CmsPost $record) {
                        if (! $record?->category) return '-';
                        $name = format_translatable($record->category->name, 'ar')
                            ?: (format_translatable($record->category->name, 'en') ?: $record->category->slug);
                        return urldecode((string) $name);
                    })
                    ->badge()
                    ->color('info')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->headerActions([
                CreateAction::make(),
                Action::make('sync_wp_blog_posts')
                    ->label('Sync from WordPress')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Sync Blog Posts from WordPress')
                    ->modalDescription('Do you want to fetch and update blog posts directly from grassflorist.com?')
                    ->action(function (StoreImportService $importService) {
                        try {
                            $res = $importService->importBlogPosts();
                            Notification::make()
                                ->title('Blog Posts Synced!')
                                ->body("Successfully imported/updated {$res['imported']} blog posts.")
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
            'index' => Pages\ListCmsPosts::route('/'),
            'create' => Pages\CreateCmsPost::route('/create'),
            'edit' => Pages\EditCmsPost::route('/{record}/edit'),
        ];
    }
}
