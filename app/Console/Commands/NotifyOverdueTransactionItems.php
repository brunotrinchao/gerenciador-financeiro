<?php

namespace App\Console\Commands;

use App\Helpers\TranslateString;
use App\Mail\DueTodayTransactionItemsMail;
use App\Mail\OverdueTransactionItemsMail;
use App\Models\TransactionItem;
use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class NotifyOverdueTransactionItems extends Command
{
    protected $signature = 'transactions:notify-overdue';
    protected $description = 'Notifica o usuário sobre transações em atraso com status PENDING';

    public function handle(): void
    {
        $items = TransactionItem::with('transaction')->where('status', 'PENDING')->whereDate('due_date', '<', now())
            ->get();

        if ($items->isEmpty()) {
            $this->info('Nenhuma transação em atraso encontrada.');
            return;
        }

        $itemsByFamily = $items->groupBy(fn($item) => $item->transaction->family_id);

        foreach ($itemsByFamily as $familyId => $familyItems) {
            // Busca todos os usuários da família
            $emails = User::where('family_id', $familyId)->pluck('email')->toArray();

            if (empty($emails)) {
                $this->warn("Nenhum usuário encontrado para a família {$familyId}.");
                continue;
            }

            // Monta os dados do e-mail dessa família
            $htmlEmail = [];
            foreach ($familyItems as $item) {
                $transaction = $item->transaction;

                $amount = number_format($item->amount, 2, ',', '.');
                $description = $transaction->description;
                $dueDate = \Illuminate\Support\Carbon::parse($item->due_date)->format('d/m/Y');
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

                // Opcional: notificação no banco para cada item
                foreach (User::where('family_id', $familyId)->get() as $recipient) {
                    Notification::make()
                        ->title('Transação em atraso')
                        ->body("Valor: R$ {$amount}<br>Produto: {$description}<br>Vencimento: {$dueDate}<br>Método: {$method}<br>Status: {$status}<br>Parcela: {$installment}/{$recurrence_interval}")
                        ->icon('heroicon-o-exclamation-circle')
                        ->iconColor('danger')
                        ->sendToDatabase($recipient);
                }
            }
            // Envia o e-mail para todos os usuários da família
            Mail::to($emails)->send(new DueTodayTransactionItemsMail($htmlEmail));

            $this->info("Família {$familyId}: notificadas {$familyItems->count()} transações em atraso.");
        }
    }
}
