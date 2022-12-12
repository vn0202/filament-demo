<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArticleResource\Pages;
use App\Filament\Resources\ArticleResource\RelationManagers;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use App\Forms\Components\Textarea;
use Filament\Tables\Filters\SelectFilter;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Mohamedsabil83\FilamentFormsTinyeditor\Components\TinyEditor;
use Illuminate\Database\Eloquent\Collection;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = "News";

    public static function form(Form $form): Form
    {
        $year = date('Y');
        $month = date('m');
        $day = date('d');
        $directory = "uploads/thumbs/" . $year ."-".$month .'-'.$day;
        return $form
            ->schema([
                //
                Forms\Components\TextInput::make('title')->required()->unique(ignoreRecord:true)->columnSpanFull(),
                Forms\Components\TextInput::make('description')->required()->columnSpanFull(),
                TinyEditor::make('content')->columnSpanFull(),
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'title')
                    ->createOptionForm([
                        Forms\Components\TextInput::make('title')
                            ->afterStateUpdated(fn (\Closure $set, $state )=>$set('slug',Str::of($state)->slug('-'))
                            )
                            ->required()
                            ->unique(),
                        Forms\Components\Hidden::make('slug')->unique()->validationAttribute('title'),

                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'title')

                    ]),
               Forms\Components\Select::make('tags')
                    ->multiple()
                   ->relationship('tags', 'name')
                   ->createOptionForm([
                       Forms\Components\TextInput::make('name')
                           ->afterStateUpdated(fn (\Closure $set, $state )=>$set('slug',Str::of($state)->slug('-'))
                           )
                           ->required()
                           ->unique(),
                       Forms\Components\Hidden::make('slug')->unique()->validationAttribute('title'),
                   ]),
               Forms\Components\FileUpload::make('thumb')->image()->required()->columnSpanFull()
                ->disk('public')->directory($directory),
                Forms\Components\Radio::make('active')
                    ->options([
                        '0' => 'Draft',
                        '1' => 'Published'
                    ])->default('0')
            ]);
    }

    public static function table(Table $table): Table
    {
        $array_action = [];
        return $table
            ->columns([
                //
            Tables\Columns\ImageColumn::make('thumb')->disk('public'),

                Tables\Columns\TextColumn::make('title')->limit(25)->sortable()->searchable(),
                Tables\Columns\TextColumn::make('description')->limit(25)->sortable()->searchable(),
                Tables\Columns\TextColumn::make('category.title')->sortable()->searchable()->url(fn (Article $record): string => route('filament.resources.articles.index', ['tableFilters[category_id][value]' => $record->category_id])),
                Tables\Columns\TextColumn::make('users.name')->label('Author')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('active')->enum([
                    '0' => 'Draft',
                    '1' => 'Published',
                ])
             ->sortable()->searchable(),
            ])


            ->filters([
                //
                SelectFilter::make('category_id')->relationship('category', 'title'),
                SelectFilter::make('tag_id')->relationship('tags', 'name'),
                SelectFilter::make('active')
                    ->options([
                        '0' => 'Draft',
                        '1' => 'Published',
                    ])  ->visible(fn (Article $record): bool => auth()->user()->can('publish', $record))

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                // publish all choice
                Tables\Actions\BulkAction::make('active')
                    ->action(function (Collection $records){
                        foreach ($records as $record)
                        {
                            $record->change($record);
                        }
                    })->label('Publish')
                    ->requiresConfirmation()
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->visible(fn (Article $record): bool => auth()->user()->can('articles.publish', $record))
                ,
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
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}
