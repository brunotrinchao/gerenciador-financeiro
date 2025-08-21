<?php

namespace App\Console\Commands;

use App\Helpers\TranslateString;
use App\Mail\DueTodayTransactionItemsMail;
use App\Models\TransactionItem;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class NotifyDueTodayTransactionItems extends Command
{
    protected $signature = 'transactions:notify-due-today';
    protected $description = 'Notifica o usuário sobre transações que vencem hoje com status PENDING';


    /**
     * Execute the console command.
     */
    public function handle()
    {
        $items = TransactionItem::with('transaction')
            ->where('status', 'PENDING')
            ->whereHas('transaction', function ($query) {
                $query->where('type', 'EXPENSE');
            })
            ->whereDate('due_date', now()->toDateString())
            ->get();

        if ($items->isEmpty()) {
            $this->info('Nenhuma transação com vencimento hoje.');
            return;
        }

        // Agrupa os items pelo family_id da transação
        $itemsByFamily = $items->groupBy(fn($item) => $item->transaction->family_id);

        foreach ($itemsByFamily as $familyId => $familyItems) {
            // Busca todos os usuários da família
            $emails = User::where('family_id', $familyId)->pluck('email')->toArray();

            if (empty($emails)) {
                $this->warn("Nenhum usuário encontrado para a família {$familyId}.");
                continue;
            }

            $htmlEmail = [];

            foreach ($familyItems as $item) {
                $transaction = $item->transaction;

                $amount = number_format($item->amount, 2, ',', '.');
                $description = $transaction->description;
                $dueDate = Carbon::parse($item->due_date)->format('d/m/Y');
                $method = TranslateString::getMethod($item);
                $status = TranslateString::getStatusLabel($item->status);
                $installment = $item->installment_number;
                $recurrence_interval = $transaction->recurrence_interval;

                $htmlEmail[] = [
                    'amount' => $amount,
                    'description' => $description,
                    'due_date' => $dueDate,
                    'method' => $method,
                    'installment' => "{$installment}/{$recurrence_interval}",
                    'status' => $status,
                ];

                // Notificações no banco para cada usuário da família
                foreach (User::where('family_id', $familyId)->get() as $recipient) {
                    Notification::make()
                        ->title('Transação vence hoje')
                        ->body("Valor: R$ {$amount}<br>Produto: {$description}<br>Vencimento: {$dueDate}<br>Método: {$method}<br>Status: {$status}<br>Parcela: {$installment}/{$recurrence_interval}")
                        ->icon('heroicon-o-clock')
                        ->iconColor('warning')
                        ->sendToDatabase($recipient);
                }
            }

            // Envia o e-mail para todos os usuários da família
//            Mail::to($emails)->send(new DueTodayTransactionItemsMail($htmlEmail));

            $this->info("Família {$familyId}: notificadas {$familyItems->count()} transações com vencimento hoje.");
        }
    }

}
