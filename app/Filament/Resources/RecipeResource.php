<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecipeResource\Pages;
use App\Filament\Resources\RecipeResource\RelationManagers;
use App\Filament\Resources\RecipeResource\RelationManagers\TutorialsRelationManager;
use App\Models\Ingredient;
use App\Models\Recipe;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Wizard;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RecipeResource extends Resource
{
    protected static ?string $model = Recipe::class;

    protected static ?string $navigationGroup = 'Resources';

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Main Information')
                        ->icon('heroicon-m-information-circle')
                        ->completedIcon('heroicon-m-check')
                        ->description('Add information of your recipe.')
                        ->columns(2)
                        ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('thumbnail')
                    ->image()
                    ->directory('recipes/thumbnails')
                    ->disk('public')
                    ->required(),
                Forms\Components\Textarea::make('about')
                    ->required()
                    ->rows(10)
                    ->cols(20),
                    Forms\Components\Select::make('recipe_author_id')
                    ->relationship('author', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('url_video')
                    ->helperText('Gunakan kode video dari YouTube.')
                    ->required()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('url_file')
                    ->downloadable()
                    ->helperText('Upload resep dalam format PDF.')
                    ->uploadingMessage('Uploading recipes...')
                    ->acceptedFileTypes(['application/pdf'])
                    ->required(),
                        ]),
                    Wizard\Step::make('Ingredients')
                        ->icon('heroicon-m-arrow-trending-up')
                        ->completedIcon('heroicon-m-check')
                        ->description('Add ingredients to your recipe.')
                        ->schema([
                Forms\Components\Repeater::make('recipeIngredients')
                    ->relationship()
                    ->grid(2)
                    ->defaultItems(4)
                    ->schema([
                Forms\Components\Select::make('ingredient_id')
                            ->relationship('ingredient', 'name')
                            ->required(),
                            ]),
                        ]),
                    Wizard\Step::make('Photos')
                        ->icon('heroicon-m-photo')
                        ->completedIcon('heroicon-m-check')
                        ->description('Upload photos of your recipe.')
                        ->schema([
                Forms\Components\Repeater::make('photos')
                    ->relationship('photos')
                    ->grid(2)
                    ->defaultItems(3)
                    ->schema([
                Forms\Components\FileUpload::make('photo')
                            ->image()
                            ->directory('recipes/photos')
                            ->disk('public')
                            ->required(),
                        ]),
                    ]),
                ])
                ->columnSpan('full')
                    ->skippable()
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('author.photo')
                    ->circular(),
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->square(),
            ])
            ->filters([
                SelectFilter::make('recipe_author_id')
                    ->label('Author')
                    ->relationship('author', 'name'),
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name'),
                SelectFilter::make('ingredient_id')
                    ->label('Ingredient')
                    ->options(Ingredient::pluck('name', 'id'))
                    ->query(function (Builder $query, array $data) {
                        if ($data['value']) {
                            $query->whereHas('recipeIngredients', function (Builder $query) use ($data) {
                                $query->where('ingredient_id', $data['value']);
                            });
                        }
                    })
                    ->relationship('category', 'name')
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
        return [
            TutorialsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecipes::route('/'),
            'create' => Pages\CreateRecipe::route('/create'),
            'edit' => Pages\EditRecipe::route('/{record}/edit'),
        ];
    }
}
