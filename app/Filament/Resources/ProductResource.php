<?php

namespace App\Filament\Resources;

use App\Enums\ProductTypeEnum;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationGroup = 'Shop';


    protected static ?string $navigationLabel = 'Test';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                ->schema([
                    Section::make()
                    ->schema([
                       TextInput::make('name')
                           ->required()  // Valida que el campo sea obligatorio
                           ->reactive()  // Reactivo para validar en tiempo real
                           ->live(onBlur: true)  // Valida cuando el usuario sale del campo
                           ->unique()
                        ->afterStateUpdated(function(string $operation, $state, Set $set) {
                            if ($operation !== 'create') {
                                return;
                            }
                            $set('slug',Str::slug($state));
                                                                                          }
                                        )
                        ,
                        TextInput::make('slug')
                        ->disabled()
                        ->dehydrated()
                        ->required()
                        ->unique(Product::class, 'slug',ignoreRecord: true)
                        ,
                        MarkdownEditor::make('description')
                        ->columnSpan('full')
                        ,
                    ])->columns(2),



                    Section::make('Pricing & Inventory')
                        ->schema([
                                TextInput::make('sku')
                                ->label('SKU (Stock keeping Unit)')
                                ->unique()

                                 ->required()
                            ,
                            TextInput::make('price')->numeric()
                                ->rules('regex:/^\d{1,6}(\.\d{0,2})?$/')
                                ->required()
                            ,
                            TextInput::make('quantity')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->required()
                            ,
                            Select::make('type')
                            ->options([
                                'downloadable' => ProductTypeEnum::DOWNLOADABLE->value,
                                'deliverable' => ProductTypeEnum::DELIVERABLE->value,
                            ])->required()
                        ])->columns(2),

                ]),


                Group::make()
                    ->schema([
                        Section::make('Status')
                            ->schema([
                                    Toggle::make('is_visible')
                                        ->label('Visibility')
                                        ->helperText('Enable or disable product visibility.')
                                        ->default(true)
                                    ,
                                    Toggle::make('is_featured')
                                        ->label('Featured')
                                        ->helperText('Enable or disable product featured status.')
                                   ,
                                    DatePicker::make('published_at')
                                        ->label('Availability')
                                        ->default(now())
                            ]),

                        Section::make('Image')
                        ->schema([
                            FileUpload::make('image')
                            ->directory('form-attachments')
                            ->preserveFilenames()
                            ->image()
                            ->imageEditor()
                        ])->collapsible(),


                        Section::make('Associations')
                            ->schema([
                                 Select::make('brand_id')
                                ->relationship('brand', 'name')
                            ])
                    ])

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->toggleable()
                    ,

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                ,
                Tables\Columns\TextColumn::make('brand.name')
                    ->searchable()
                    ->sortable()
                 ->toggleable()
                ,
                Tables\Columns\IconColumn::make('is_visible')
                    ->sortable()
                    ->toggleable()
                    ->label('Visibility')
                    ->boolean(),
                Tables\Columns\TextColumn::make('price')
                    ->sortable()
                    ->toggleable()
                ,
                Tables\Columns\TextColumn::make('quantity')
                    ->sortable()
                    ->toggleable()
                ,
                Tables\Columns\TextColumn::make('published_at')
                 ->date()
                ->sortable()
                ,
                Tables\Columns\TextColumn::make('type')
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_visible')
                ->label('Visibility')
                ->boolean()
                ->trueLabel('Only Visible Products')
                ->falseLabel('Only Hidden Products')
                ->native(false)
                ,
                Tables\Filters\SelectFilter::make('brand')
                ->relationship('brand', 'name')
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
        return [
            //
        ];
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
