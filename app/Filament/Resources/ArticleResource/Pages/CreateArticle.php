<?php

namespace App\Filament\Resources\ArticleResource\Pages;

use App\Filament\Resources\ArticleResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\View\View;

use Illuminate\Support\Str;
class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['author'] = auth()->id();
        $data['slug'] = Str::of($data['title'])->slug('-');
        return $data;
    }
}
