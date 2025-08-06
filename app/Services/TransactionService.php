<?php

namespace App\Services;

use App\Enum\TransactionTypeEnum;
use App\Models\Account;
use App\Models\Card;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Transfer;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function create(array $data): void
    {
        DB::transaction(function () use ($data) {
            $data['amount'] = preg_replace('/[^0-9,]/', '', $data['amount']);

            if($data['type'] == TransactionTypeEnum::TRANSFER->value){
                $this->handleTransfer($data);
                return;
            }

            $transaction = Transaction::create($data);

            if (!$transaction) {
                return;
            }

            $this->updateBalancesBasedOnPaidInstallments($transaction, $data);
            $this->createInstallments($transaction, $data);
            $this->notifyTransactionCreated($transaction);
        });
        DB::commit();
    }


    public function update(Transaction $record, array $data): void
    {

        if(isset($data['amount']) && !empty($data['amount'])) {
            $data['amount'] = (int) preg_replace('/[^0-9,]/', '', $data['amount']);
        }

        if($record->type == TransactionTypeEnum::TRANSFER->value){
            $transfer = Transfer::where('source_transaction_id', $record->id)
                ->orWhere('target_transaction_id', $record->id)
                ->first();

            Transaction::find($transfer->source_transaction_id)->update($data);
            Transaction::find($transfer->target_transaction_id)->update($data);
        }

        $record->update($data);

        $service = new TransactionItemService();
        $service->update($record, false);
    }

    protected function updateBalancesBasedOnPaidInstallments(Transaction $transaction, array $data): void
    {
        $amountInCents = (int) str_replace(['.', ','], ['', '.'], $transaction->amount);
        $installmentsCount = !empty($data['is_recurring']) ? (int) ($transaction->recurrence_interval ?? 1) : 1;

        $baseValue = intdiv($amountInCents, $installmentsCount);
        $remaining = $amountInCents - ($baseValue * $installmentsCount);

        $paidInterval = (int) ($data['paid_interval'] ?? 0);
        $paidValue = 0;

        for ($i = 0; $i < $paidInterval; $i++) {
            $currentCents = $i === $installmentsCount - 1 ? $baseValue + $remaining : $baseValue;
            $paidValue += $currentCents;
        }

        if (!in_array($transaction->method, ['CARD', 'ACCOUNT']) || $paidValue <= 0) {
            return;
        }

        $multiplier = $transaction->type === 'EXPENSE' ? -1 : 1;
        $adjustedValue = $multiplier * $paidValue;

//        if ($transaction->method === 'CARD' && $transaction->card_id) {
//            $card = Card::find($transaction->card->id);
//            $card->balance = (int) $transaction->card->balance + $adjustedValue;
//            $card->save();
//            $transaction->account->refresh();
//        }

        if ($transaction->method === 'ACCOUNT' && $transaction->account_id) {
            $account = Account::find($transaction->account->id);
            $account->balance = (int) $transaction->account->balance + $adjustedValue;
            $account->save();
            $transaction->account->refresh();
        }
    }

    protected function createInstallments(Transaction $transaction, array $data): void
    {
        $amountInCents = (int) str_replace(['.', ','], ['', '.'], $transaction->amount);
        $installmentsCount = !empty($data['is_recurring']) ? (int) ($transaction->recurrence_interval ?? 1) : 1;

        $baseValue = intdiv($amountInCents, $installmentsCount);
        $remaining = $amountInCents - ($baseValue * $installmentsCount);

        $date = Carbon::parse($data['date']);
        $cardDueDay = ($transaction->method === 'CARD' && $transaction->card?->due_date)
            ? (int) $transaction->card->due_date
            : null;

        $paidInterval = (int) ($data['paid_interval'] ?? 0);
        for ($i = 0; $i < $installmentsCount; $i++) {
            $currentCents = $i === $installmentsCount - 1 ? $baseValue + $remaining : $baseValue;
            $paymentDate = (clone $date)->addMonths($i);

            if ($cardDueDay) {
                $paymentDate->day = min($cardDueDay, $paymentDate->daysInMonth);
            }

            $isPaid = $paidInterval > 0 && $i + 1 <= $paidInterval;
            $status = $isPaid
                ? 'PAID'
                : (in_array($transaction->method, ['CARD', 'ACCOUNT']) ? 'DEBIT' : 'PENDING');

            TransactionItem::create([
                'transaction_id'     => $transaction->id,
                'due_date'           => $paymentDate,
                'payment_date'       => $isPaid ? $paymentDate : null,
                'amount'             => $currentCents,
                'installment_number' => $i + 1,
                'status'             => $status,
            ]);
        }
    }

    protected function notifyTransactionCreated(Transaction $transaction): void
    {
        Notification::make()
            ->title(__('forms.notifications.transaction_created_title'))
            ->body(trans_choice(
                __('forms.notifications.transaction_created_body'),
                $transaction->recurrence_interval,
                ['count' => $transaction->recurrence_interval]
            ))
            ->success()
            ->send();
    }

    protected function handleTransfer(array $data): void
    {
        $dataOrigin = $data;
        $dataTarget = $data;
        $origin = null;

        unset($dataOrigin['origin_account_id'], $dataOrigin['target_account_id']);
        unset($dataTarget['origin_account_id'], $dataTarget['target_account_id']);

        // Criação da transação de origem (somente se não for dinheiro)
        if ($data['method'] !== 'CASH') {
            $accountOrigin = Account::find($data['origin_account_id']);
            $dataOrigin['account_id'] = $accountOrigin?->id;
            $dataOrigin['description'] = "Origem: {$accountOrigin?->bank->name}. | {$data['description']}";

            $origin = Transaction::create($dataOrigin);
            $this->handleTransferSide($origin, true);
        }

        // Criação da transação de destino (sempre ocorre)
        $accountTarget = Account::find($data['target_account_id']);
        $dataTarget['account_id'] = $accountTarget?->id;
        $dataTarget['description'] = "Destino: {$accountTarget?->bank->name}. | {$data['description']}";

        $target = Transaction::create($dataTarget);
        $this->handleTransferSide($target, false);

        // Relacionamento entre as duas transações
        Transfer::create([
            'source_transaction_id' => $origin?->id ?? null,
            'target_transaction_id' => $target->id,
        ]);

        // Notifica com base na origem (se houver), senão na de destino
        $this->notifyTransactionCreated($origin ?? $target);
    }


    protected function createTransactionItem(Transaction $transaction, int $amount, Carbon $date): void
    {
        TransactionItem::create([
            'transaction_id' => $transaction->id,
            'due_date' => $date,
            'payment_date' => $date,
            'amount' => $amount,
            'installment_number' => 1,
            'status' => 'PAID',
        ]);
    }

    protected function handleTransferSide(Transaction $transaction, bool $isOrigin): void
    {
        $amount = $transaction->amount;
        $date = Carbon::parse($transaction->date);
        $sign = $isOrigin ? -1 : 1;

        // Atualiza saldo
        if (in_array($transaction->method, ['ACCOUNT','CASH']) && $transaction->account_id) {
            $account = $transaction->account()->lockForUpdate()->first();
            $account->balance = (int) $account->balance + ($sign * $amount);
            $account->save();
        }

        $this->createTransactionItem($transaction, $amount, $date);
    }


}
