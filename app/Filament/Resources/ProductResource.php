<?php

namespace App\Filament\Resources;

use App\Enums\ProductStatus;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Store';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'sku'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'SKU' => $record->sku,
            'Status' => $record->status?->label(),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Product Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\RichEditor::make('description')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('vendor_profile_id')
                            ->relationship('vendorProfile', 'business_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('product_category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\Select::make('status')
                            ->options(ProductStatus::class)
                            ->required()
                            ->default(ProductStatus::Draft),
                    ])->columns(2),

                Forms\Components\Section::make('Pricing')
                    ->schema([
                        Forms\Components\TextInput::make('price_cents')
                            ->label('Price ($)')
                            ->numeric()
                            ->required()
                            ->prefix('$')
                            ->afterStateHydrated(fn ($component, $state) => $component->state($state ? $state / 100 : null))
                            ->dehydrateStateUsing(fn ($state) => $state ? (int) round($state * 100) : null),
                        Forms\Components\TextInput::make('member_price_cents')
                            ->label('Member Price ($)')
                            ->numeric()
                            ->nullable()
                            ->prefix('$')
                            ->afterStateHydrated(fn ($component, $state) => $component->state($state ? $state / 100 : null))
                            ->dehydrateStateUsing(fn ($state) => $state ? (int) round($state * 100) : null),
                        Forms\Components\TextInput::make('shipping_fee_cents')
                            ->label('Shipping Fee ($)')
                            ->numeric()
                            ->nullable()
                            ->prefix('$')
                            ->afterStateHydrated(fn ($component, $state) => $component->state($state ? $state / 100 : null))
                            ->dehydrateStateUsing(fn ($state) => $state ? (int) round($state * 100) : null),
                    ])->columns(3),

                Forms\Components\Section::make('Inventory')
                    ->schema([
                        Forms\Components\TextInput::make('stock_quantity')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Toggle::make('track_stock')
                            ->label('Track Stock')
                            ->default(false),
                        Forms\Components\Toggle::make('is_digital')
                            ->label('Digital Product')
                            ->default(false),
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured')
                            ->default(false),
                    ])->columns(2),

                Forms\Components\Section::make('Media')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('images')
                            ->collection('images')
                            ->multiple()
                            ->image()
                            ->reorderable()
                            ->maxSize(10240)
                            ->imageResizeMode('contain')
                            ->imageResizeTargetWidth(1920)
                            ->imageResizeTargetHeight(1920)
                            ->imageResizeUpscale(false),
                        SpatieMediaLibraryFileUpload::make('digital_file')
                            ->collection('digital_file')
                            ->label('Digital File'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vendorProfile.business_name')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('price_cents')
                    ->label('Price')
                    ->formatStateUsing(fn ($state): string => '$' . number_format($state / 100, 2))
                    ->sortable(),
                Tables\Columns\TextColumn::make('shipping_fee_cents')
                    ->label('Shipping')
                    ->formatStateUsing(fn ($state): string => $state ? '$' . number_format($state / 100, 2) : 'Default')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_digital')
                    ->boolean()
                    ->label('Digital'),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->label('Featured'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(ProductStatus::class),
                Tables\Filters\SelectFilter::make('vendor_profile_id')
                    ->relationship('vendorProfile', 'business_name')
                    ->label('Vendor')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('product_category_id')
                    ->relationship('category', 'name')
                    ->label('Category')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_digital')
                    ->label('Digital'),
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
