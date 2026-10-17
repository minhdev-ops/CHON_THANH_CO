<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Toggle::make('is_active')
                    ->label('Hiển thị')
                    ->default(true),
                TextInput::make('order')
                    ->numeric()
                    ->default(0),
                // Title (localized JSON)
                Repeater::make('title')
                    ->label('Tiêu đề')
                    ->schema([
                        TextInput::make('key')
                            ->label('Ngôn ngữ (vi/en)')
                            ->required()
                            ->maxLength(2),
                        TextInput::make('value')
                            ->label('Giá trị')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->collapsible()
                    ->addActionLabel('Thêm ngôn ngữ')
                    ->columnSpan('full'),
                // Content blocks (JSON array)
                Repeater::make('content')
                    ->label('Các khối nội dung')
                    ->schema([
                        TextInput::make('type')
                            ->required()
                            ->maxLength(50),
                        // Text block
                        RichEditor::make('text')
                            ->label('Nội dung văn bản')
                            ->required()
                            ->columnSpan('full')
                            ->visible(fn ($state) => $state['type'] === 'text'),
                        // Image block
                        TextInput::make('image')
                            ->label('URL ảnh')
                            ->required()
                            ->visible(fn ($state) => $state['type'] === 'image'),
                        // Gallery block (array of images)
                        Repeater::make('gallery')
                            ->label('Thư viện ảnh')
                            ->schema([
                                TextInput::make('image')
                                    ->label('URL ảnh')
                                    ->required(),
                            ])
                            ->visible(fn ($state) => $state['type'] === 'gallery'),
                    ])
                    ->collapsible()
                    ->addActionLabel('Thêm khối')
                    ->columnSpan('full'),
                // SEO meta
                Repeater::make('meta_title')
                    ->label('Meta Title')
                    ->schema([
                        TextInput::make('key')
                            ->label('Ngôn ngữ (vi/en)')
                            ->required()
                            ->maxLength(2),
                        TextInput::make('value')
                            ->label('Giá trị')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->collapsible()
                    ->addActionLabel('Thêm ngôn ngữ')
                    ->columnSpan('full'),
                Repeater::make('meta_description')
                    ->label('Meta Description')
                    ->schema([
                        TextInput::make('key')
                            ->label('Ngôn ngữ (vi/en)')
                            ->required()
                            ->maxLength(2),
                        Textarea::make('value')
                            ->label('Giá trị')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->collapsible()
                    ->addActionLabel('Thêm ngôn ngữ')
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('slug')->searchable()->sortable(),
                BooleanColumn::make('is_active')->label('Hiển thị'),
                TextColumn::make('order')->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
