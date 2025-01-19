<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrainerResource\Pages;
use App\Filament\Resources\TrainerResource\RelationManagers;
use App\Models\Trainer;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TrainerResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Trainer';
    protected static ?string $label = 'Trainer';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', 3);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                
                TextInput::make('phone')
                    ->tel()
                    ->maxLength(15),
                Select::make('gender')
                    ->options([
                        '0' => 'Male',
                        '1' => 'Female',
                    ])
                    ->required(),
                DatePicker::make('birth_date')
                    ->required(),
                
                TextInput::make('fees')
                    ->numeric()
                    ->required(),
                Select::make('topics')
                    ->label('Areas')
                    ->multiple(true)
                    ->preload()
                    ->relationship('topics', 'name'),
                RichEditor::make('notes')->columnSpan(2),
                SpatieMediaLibraryFileUpload::make('certificates')
                    ->multiple()
                    ->collection('users'),
                SpatieMediaLibraryFileUpload::make('image')
                    ->collection('users'),
                Toggle::make('active'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                SpatieMediaLibraryImageColumn::make('image'),

                TextColumn::make('email')->sortable()->searchable(),
                TextColumn::make('phone')->sortable()->searchable(),
                BooleanColumn::make('active')->label('Active'),


            ])
            ->filters([
                //
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
            'index' => Pages\ListTrainers::route('/'),
            'create' => Pages\CreateTrainer::route('/create'),
            'edit' => Pages\EditTrainer::route('/{record}/edit'),
        ];
    }
}
