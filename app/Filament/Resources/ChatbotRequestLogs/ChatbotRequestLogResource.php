<?php

namespace App\Filament\Resources\ChatbotRequestLogs;

use App\Filament\Resources\ChatbotRequestLogs\Pages\ManageChatbotRequestLogs;
use App\Models\ChatbotRequestLog;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ChatbotRequestLogResource extends Resource
{
    protected static ?string $model = ChatbotRequestLog::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static \UnitEnum|string|null $navigationGroup = 'Sistem & Konfigurasi';

    protected static ?string $navigationLabel = 'Log Chatbot AI';

    protected static ?string $modelLabel = 'Log Chatbot AI';

    protected static ?string $pluralModelLabel = 'Log Chatbot AI';

    protected static ?int $navigationSort = 5;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('Detail Request')
                    ->schema([
                        TextInput::make('created_at')
                            ->label('Waktu')
                            ->disabled(),
                        TextInput::make('status')
                            ->label('Status')
                            ->disabled(),
                        TextInput::make('provider')
                            ->label('Provider')
                            ->disabled(),
                        TextInput::make('model')
                            ->label('Model')
                            ->disabled(),
                    ])
                    ->columns(2),
                Textarea::make('message')
                    ->label('Pesan Request (Prompt)')
                    ->disabled()
                    ->rows(4)
                    ->columnSpanFull(),
                Textarea::make('response')
                    ->label('Response / Hasil')
                    ->disabled()
                    ->rows(6)
                    ->columnSpanFull()
                    ->visible(fn ($record) => $record?->status === 'success'),
                Textarea::make('error_message')
                    ->label('Detail Error')
                    ->disabled()
                    ->rows(6)
                    ->columnSpanFull()
                    ->visible(fn ($record) => $record?->status === 'failed'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Pengguna')
                    ->default('System / Anonymous')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('message')
                    ->label('Pesan Request')
                    ->limit(40)
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('provider')
                    ->label('Provider')
                    ->badge()
                    ->color('gray')
                    ->searchable(),
                TextColumn::make('model')
                    ->label('Model')
                    ->searchable(),
                TextColumn::make('latency_seconds')
                    ->label('Latency')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) . 's' : '-')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'success' => 'Success',
                        'failed' => 'Failed',
                    ]),
                SelectFilter::make('provider')
                    ->label('Provider')
                    ->options([
                        'gemini' => 'Gemini',
                        'openai' => 'OpenAI',
                        'groq' => 'Groq',
                        'anthropic' => 'Anthropic',
                        'deepseek' => 'DeepSeek',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageChatbotRequestLogs::route('/'),
        ];
    }
}

