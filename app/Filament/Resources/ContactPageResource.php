<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactPageResource\Pages;
use App\Filament\Resources\ContactPageResource\RelationManagers;
use App\Models\ContactPage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Textarea;

class ContactPageResource extends Resource
{
    protected static ?string $model = ContactPage::class;
     protected static ?string $navigationGroup = 'Pages';
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center';
    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('contact_page') ?? false;
    }

    public static function getRecordTitle(?\Illuminate\Database\Eloquent\Model $record): ?string
    {
        return 'Contact Page';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Update Information')
                    ->schema([
                        Forms\Components\Placeholder::make('last_updated_at')
                            ->label('Last Updated')
                            ->content(function (?ContactPage $record) {
                                return $record?->updated_at
                                    ? $record->updated_at->format('d M Y, h:i A')
                                    : 'Not updated yet';
                            }),

                        Forms\Components\Placeholder::make('updated_by')
                            ->label('Updated By')
                            ->content(function (?ContactPage $record) {
                                return $record?->updatedBy?->name ?? 'Not available';
                            }),
                    ])
                    ->columns(2)
                    ->visible(fn (string $operation) => $operation === 'edit'),

                Forms\Components\Tabs::make('Contact Information')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('English (EN)')
                            ->icon('heroicon-o-language')
                            ->schema([
                                Forms\Components\TextInput::make('con_title.en')
                                    ->label('Heading / Sub Title (English)'),

                                Forms\Components\RichEditor::make('con_address.en')
                                    ->label('Address / Contact Details (English)'),
                            ]),

                        Forms\Components\Tabs\Tab::make('Arabic (عربي)')
                            ->icon('heroicon-o-globe-alt')
                            ->schema([
                                Forms\Components\TextInput::make('con_title.ar')
                                    ->label('Heading / Sub Title (Arabic)')
                                    ->extraInputAttributes(['dir' => 'rtl']),

                                Forms\Components\RichEditor::make('con_address.ar')
                                    ->label('Address / Contact Details (Arabic)')
                                    ->extraInputAttributes(['dir' => 'rtl']),
                            ]),
                    ])
                    ->columnSpanFull(),

                Forms\Components\Section::make('Communication & Location Details')
                    ->schema([
                        Forms\Components\TextInput::make('con_phone')
                            ->label('Phone Number / Mobile'),

                        Forms\Components\TextInput::make('con_email')
                            ->label('E-mail Address')
                            ->email(),

                        Forms\Components\Textarea::make('con_map')
                            ->label('Map Link / Embed URL / Location Details')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Contact Us Page SEO Metadata')
                    ->description('Search engine optimization title, description, and keywords')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('meta_tag_title.en')
                                    ->label('Meta Title (English)')
                                    ->placeholder('e.g. Contact Grass Florist'),
                                Forms\Components\TextInput::make('meta_tag_title.ar')
                                    ->label('Meta Title (Arabic - عربي)')
                                    ->extraInputAttributes(['dir' => 'rtl'])
                                    ->placeholder('اتصل بنا - جراس فلوريست'),
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
                
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
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
            'index' => Pages\ListContactPages::route('/'),
            'create' => Pages\CreateContactPage::route('/create'),
            'edit' => Pages\EditContactPage::route('/{record}/edit'),
        ];
    }
    public static function getNavigationUrl(): string
    {
        $recordId = \App\Models\ContactPage::query()->first()?->id;

        return $recordId
            ? static::getUrl('edit', ['record' => $recordId])
            : static::getUrl('index');
    }
}
