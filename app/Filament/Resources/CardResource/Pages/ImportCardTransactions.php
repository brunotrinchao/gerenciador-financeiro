<?php

namespace App\Filament\Resources\CardResource\Pages;

use App\Filament\Resources\CardResource;
use App\Models\Card;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class ImportCardTransactions extends Page implements HasForms
{

    use InteractsWithForms;
    use WithFileUploads;
    protected static string $view = 'filament.resources.card-resource.pages.import-card-transactions';

    protected static string $resource = CardResource::class;
    protected static ?string $breadcrumb = 'Importar trasaçoes do cartão (CSV)';
    protected static ?string $navigationLabel = 'Importar trasações do cartão (CSV)';
    protected static ?string $title = 'Transação';

    public ?array $transactions = [];

    public ?Card $selectedCard = null;
    public $cards;

    /** @var TemporaryUploadedFile|null */
    public $csvFile = null;

    public ?int $recordId;
    public ?int $cardId = null;

    public ?Category $category = null;

    public function mount(Card $record): void
    {
        $this->recordId = $record->id;
        if($this->recordId){
            $this->selectedCard = Card::findOrFail($this->recordId);
        }
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            FileUpload::make('csvFile')
                ->label('Arquivo CSV')
                ->acceptedFileTypes(['text/csv'])
                ->required()
                ->directory('temp-uploads')
                ->multiple(false) ,
        ];
    }

    public function parseCsv()
    {
//        $this->validate([
//            'selectedCard' => 'required|exists:cards,id',
//            'csvFile' => 'required|file|mimes:csv,txt',
//        ]);

        $file = is_array($this->csvFile) ? reset($this->csvFile) : $this->csvFile;

        $handle = fopen($file->getRealPath(), 'r');

        $this->transactions = [];

        // Lê o cabeçalho (header)
        $headers = fgetcsv($handle, 1000, ';');

        while (($row = fgetcsv($handle, 1000, ';')) !== false) {
            $data = array_combine($headers, $row);

            $this->transactions[] = [
                'description' => $data['Nome da Movimentação'] ?? '',
                'total_installments' => (int) ($data['Parcelas Totais'] ?? 0),
                'paid_installments' => (int) ($data['Parcelas Pagas'] ?? 0),
                'pending_installments' => (int) ($data['Parcelas a Pagar'] ?? 0),
                'total_amount' => (float) str_replace(',', '.', $data['Valor Total'] ?? 0),
                'installment_amount' => (float) str_replace(',', '.', $data['Valor da Parcela'] ?? 0),
                'month' => (int) ($data['Mês'] ?? date('m')),
                'year' => (int) ($data['Ano'] ?? date('Y')),
            ];
        }

        fclose($handle);
    }

    public function import()
    {
        if (!$this->selectedCard) {
            $this->addError('selectedCard', 'Selecione um cartão antes de importar.');
            return;
        }

        $this->category = Category::where('name', 'Cartão de crédito')->first();

        foreach ($this->transactions as $data) {
            $transaction = Transaction::create([
                'description' => $data['description'],
                'type' => 'EXPENSE',
                'category_id' => $this->category->id,
                'method' => 'CARD',
                'card_id' => $this->selectedCard->id,
                'amount' => (int) round(str_replace(',', '.', $data['total_amount']) * 100),
                'is_recurring' => true,
                'date' => Carbon::parse($data['year'] . '-' . $data['month'] . '-' . $this->selectedCard->due_date)->format('Y-m-d'),
                'recurrence_interval' => $data['total_installments'],
                'recurrence_type' => 'MONTHLY',
                'user_id' => auth()->id(),
            ]);

            $installmentsCount = $data['total_installments'];

            $baseValue = intdiv($transaction->amount, $transaction->recurrence_interval);
            $remaining = $transaction->amount - ($baseValue * $installmentsCount);

            $date = Carbon::create($transaction->date);

            $cardDueDay = (int) $this->selectedCard->due_date;

//            if ($cardDueDay) {
//                $diffDays = $cardDueDay - $date->day;
//                if ($diffDays >= 0 && $diffDays <= 5) {
//                    $date->addMonth();
//                }
//            }

            for ($i = 0; $i < $installmentsCount; $i++) {
                $currentAmount = $i === $installmentsCount - 1 ? $baseValue + $remaining : $baseValue;
                $paymentDate = (clone $date)->addMonths($i);

                if ($cardDueDay) {
                    $paymentDate->day = min($cardDueDay, $paymentDate->daysInMonth);
                }

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'due_date' => $paymentDate,
                    'payment_date' => ($i + 1) < $data['paid_installments'] ? $paymentDate : null,
                    'amount' =>  (float) $currentAmount,
                    'installment_number' => $i + 1,
                    'status' => ($i + 1) < $data['paid_installments'] ? 'PAID' : 'DEBIT',
                ]);
            }

        }

        Notification::make()
            ->title('Importação concluída com sucesso!')
            ->success()
            ->send();

        return redirect(CardResource::getUrl('view', [$this->selectedCard->id]));

    }

}
