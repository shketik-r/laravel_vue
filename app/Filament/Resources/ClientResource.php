<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                // Числовое поле для возраста (обязательное)
                Components\TextInput::make('age')
                    ->numeric()
                    ->required(),

                Components\Select::make('category_id')
                    ->label('Категория клиента')
                    ->relationship('category', 'title') // Автоматически скачает список из таблицы categories!
                    ->preload()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Выводим ID, делаем его сортируемым
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),

                // Выводим Имя, добавляем по нему живой поиск
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                // Выводим Возраст, делаем сортируемым
                Tables\Columns\TextColumn::make('age')
                    ->sortable(),

                // Выводим Дату создания в красивом формате
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.title')
                    ->label('Категория')
                    ->badge() // Сделает красивый цветной бейдж
                    ->color('info')
                    ->sortable(),
            ])
            ->filters([
                // Сюда в будущем мы добавим фильтр по категориям
            ])
            ->actions([
                Tables\Actions\EditAction::make(), // Кнопка редактирования в строке
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(), // Массовое удаление чекбоксами
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
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
