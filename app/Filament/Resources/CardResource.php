<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CardResource\Pages;
use App\Filament\Resources\CardResource\RelationManagers;
use App\Helpers\Filament\ActionHelper;
use App\Models\Bank;
use App\Models\Card;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;

class CardResource extends Resource
{
    protected static ?string $navigationGroup = 'Financeiro';
    protected static ?string $model = Card::class;

//    protected static ?string $navigationIcon = 'bi-credit-card-fill';

    protected static ?string $pluralModelLabel = 'Cartões de crédito'; // Listagem

    protected static ?string $modelLabel = 'Cartão de crédito'; // Criação/Edição

    public static function form(Form $form): Form
    {
        $moneyMask = RawJs::make('    function($input){
            let value = $input.replace(/\\D/g, \'\');
            value = (value / 100).toFixed(2);
            value = value.replace(\'.\', \',\');
            value = value.replace(/\\B(?=(\\d{3})+(?!\\d))/g, \'.\');
            return value;
        }');

        return $form
            ->schema([
                Select::make('bank_id')
                    ->label('Banco')
                    ->prefixIcon('phosphor-bank')
                    ->options(Bank::pluck('name', 'id')),
                TextInput::make('name')
                    ->label('Nome'),
                TextInput::make('number')
                    ->prefixIcon('heroicon-m-credit-card')
                    ->label('Número')
                    ->mask(RawJs::make(<<<'JS'
                        $input.startsWith('34') || $input.startsWith('37') ? '9999 999999 99999' : '9999 9999 9999 9999'
                    JS)),
                Select::make('brand_id')
                    ->label('Bandeira')
                    ->searchable()
                    ->relationship('brand', 'name')
                    ->preload(),
                TextInput::make('due_date')
                    ->label('Vencimento')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(31),
                TextInput::make('limit')
                    ->label('Limite')
                    ->prefix('R$')
//                    ->formatStateUsing($decimalStateFormating)
                    ->mask($moneyMask)
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('brand.brand')
                    ->label('Bandeira')
                    ->height(30)
                    ->stacked(),
                TextColumn::make('name')
                    ->label('Nome'),
                TextColumn::make('number')
                    ->label('Número'),
//                TextColumn::make('brand')
//                    ->label('Bandeira'),
                TextColumn::make('bank.name')
                    ->label('Banco'),
                TextColumn::make('limit')
                    ->label('Limite')
                    ->money('BRL', locale: 'pt_BR'),
                TextColumn::make('due_date')
                    ->label('Vencimento')
                    ->alignCenter(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionHelper::makeSlideOver(
                    name: 'editCard',
                    form: [
                        Select::make('bank_id')
                            ->required()
                            ->label('Banco')
                            ->prefixIcon('phosphor-bank')
                            ->options(Bank::pluck('name', 'id')),
                        TextInput::make('name')
                            ->required()
                            ->label('Nome'),
                        TextInput::make('number')
                            ->required()
                            ->prefixIcon('heroicon-m-credit-card')
                            ->label('Número')
                            ->mask(RawJs::make(<<<'JS'
                        $input.startsWith('34') || $input.startsWith('37') ? '9999 999999 99999' : '9999 9999 9999 9999'
                    JS)),
                        Select::make('brand_id')
                            ->required()
                            ->label('Bandeira')
                            ->searchable()
                            ->relationship('brand', 'name')
                            ->preload(),
                        TextInput::make('due_date')
                            ->required()
                            ->label('Vencimento')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(31),
                        TextInput::make('limit')
                            ->label('Limite')
                            ->prefix('R$')
                            ->mask(RawJs::make('    function($input){
                                    let value = $input.replace(/\\D/g, \'\');
                                    value = (value / 100).toFixed(2);
                                    value = value.replace(\'.\', \',\');
                                    value = value.replace(/\\B(?=(\\d{3})+(?!\\d))/g, \'.\');
                                    return value;
                                }'))
                            ->default(0),
                    ],
                    modalHeading: 'Editar cartão',
                    label: 'Editar',
                    fillForm: fn ($record) => [
                        'bank_id'   => $record->bank_id,
                        'name'      => $record->name,
                        'number'    => $record->number,
                        'brand_id'  => $record->brand_id,
                        'due_date'  => $record->due_date,
                        'limit'     => number_format($record->limit, 2, ',', '.')
                    ]
                ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(null)
            ->recordAction('editCard')
            ->headerActions([
                ActionHelper::makeSlideOver(
                    name: 'createAccount',
                    form: [
                        Select::make('bank_id')
                            ->required()
                            ->label('Banco')
                            ->prefixIcon('phosphor-bank')
                            ->options(Bank::pluck('name', 'id')),
                        TextInput::make('name')
                            ->required()
                            ->label('Nome'),
                        TextInput::make('number')
                            ->required()
                            ->prefixIcon('heroicon-m-credit-card')
                            ->label('Número')
                            ->mask(RawJs::make(<<<'JS'
                        $input.startsWith('34') || $input.startsWith('37') ? '9999 999999 99999' : '9999 9999 9999 9999'
                    JS)),
                        Select::make('brand_id')
                            ->required()
                            ->label('Bandeira')
                            ->searchable()
                            ->relationship('brand', 'name')
                            ->preload(),
                        TextInput::make('due_date')
                            ->required()
                            ->label('Vencimento')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(31),
                        TextInput::make('limit')
                            ->label('Limite')
                            ->prefix('R$')
                            ->mask(RawJs::make('    function($input){
                                    let value = $input.replace(/\\D/g, \'\');
                                    value = (value / 100).toFixed(2);
                                    value = value.replace(\'.\', \',\');
                                    value = value.replace(/\\B(?=(\\d{3})+(?!\\d))/g, \'.\');
                                    return value;
                                }'))
                            ->default(0),
                    ],
                    modalHeading: 'Nova conta bancária',
                    label: 'Criar',
                    action: function (array $data, Action $action) {
                        $card = Card::where('name', $data['name'])->where('bank_id', $data['bank_id'])->where('brand_id', $data['brand_id'])
                            ->with(['bank', 'brand'])->first();
                        if ($card->count() > 0) {
                            Notification::make()
                                ->title('Cartão já existe')
                                ->body("Já existe uma cartão '{$card->name}' do banco {$card->bank->name} e bandeira {$card->brand->name} cadastrado.")
                                ->danger()
                                ->send();

                            $action->cancel();
                            return;
                        }

                        Card::create($data);

                        Notification::make()
                            ->title('Cartão criada')
                            ->body('A nova cartão foi cadastrada com sucesso.')
                            ->success()
                            ->send();
                    }
                ),
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
            'index' => Pages\ListCards::route('/'),
            'create' => Pages\CreateCard::route('/create'),
            'edit' => Pages\EditCard::route('/{record}/edit'),
        ];
    }
}
