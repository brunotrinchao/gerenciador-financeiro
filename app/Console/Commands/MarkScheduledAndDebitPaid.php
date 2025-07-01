<?php

namespace App\Console\Commands;

use App\Models\ActionLog;
use App\Models\TransactionItem;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MarkScheduledAndDebitPaid extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:update-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Marca transações agendadas e débito automático como pagas no dia seguinte';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hoje = Carbon::today();

        DB::beginTransaction();

        try {
            $transactionsToUpdate = TransactionItem::whereIn('status', ['SCHEDULED', 'DEBIT'])
                ->whereDate('payment_date', '<=', $hoje)
                ->get();

            $count = $transactionsToUpdate->count();

            if ($count === 0) {
                ActionLog::create([
                    'action' => 'updated-bot',
                    'description' => '(BOT) Nenhuma transação para atualizar em ' . $hoje->toDateString(),
                    'performed_at' => now(),
                ]);

                $this->info('Nenhuma transação para atualizar.');
                DB::commit();
                return 0;
            }

            foreach ($transactionsToUpdate as $transaction) {
                $oldStatus = $transaction->status;
                $oldPaymentDate = $transaction->payment_date;
                $oldValues = $transaction->toArray();

                $transaction->status = 'PAID';
                $transaction->payment_date = $hoje;
                $transaction->save();

                ActionLog::create([
                    'action' => 'updated',
                    'description' => "(BOT) Transação ID {$transaction->id} atualizada: "
                        . "status alterado de '{$oldStatus}' para 'PAID'; "
                        . "data de pagamento alterada de '"
                        . ($oldPaymentDate ? Carbon::parse($oldPaymentDate)->format('d/m/Y') : 'null')
                        . "' para '{$hoje->format('d/m/Y')}'.",

                    'model_type' => TransactionItem::class,
                    'model_id' => $transaction->id,
                    'old_values' => $oldValues,
                    'new_values' => $transaction->toArray(),
                ]);
            }

            DB::commit();

            $this->info("Atualizadas {$count} transações.");
            return 0;

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("Erro ao atualizar transações: {$e->getMessage()}");

            ActionLog::create([
                'action' => 'error',
                'description' => '(BOT) Erro ao atualizar transações: ' . $e->getMessage(),
                'performed_at' => now(),
            ]);

            return 1;
        }
    }
}
