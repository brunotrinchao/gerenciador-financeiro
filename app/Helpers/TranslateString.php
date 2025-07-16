<?php

namespace App\Helpers;

use App\Models\TransactionItem;

class TranslateString
{
    public static function formatRolePermission(string $permission): string
    {
        [$action, $entity] = explode(' ', $permission);

        $actionMap = [
            'view' => 'Visualizar',
            'create' => 'Criar',
            'edit' => 'Editar',
            'delete' => 'Excluir',
            'access' => 'Acessar',
        ];

        $entityMap = [
            'transactions' => 'Transações',
            'transaction_items' => 'Itens da Transação',
            'accounts' => 'Contas',
            'users' => 'Usuários',
            'roles' => 'Perfis',
            'dashboard' => 'Dashboard',
        ];

        $actionLabel = $actionMap[$action] ?? ucfirst($action);
        $entityLabel = $entityMap[$entity] ?? ucfirst($entity);

        return "{$actionLabel} - {$entityLabel}";
    }

    public static function getMethod(TransactionItem $item): string
    {
        return match ($item->transaction->method) {
            'CARD' => 'Cartão de crédito',
            'ACCOUNT' => 'Conta',
            'CASH' => 'Dinheiro',
            default => 'Indefinido',
        };
    }

    public static function getStatusLabel(string $status): string
    {
        return match ($status) {
            'PAID' => 'Pago',
            'SCHEDULED' => 'Agendado',
            'DEBIT' => 'Débito automático',
            'PENDING' => 'Pendente',
            default => 'Desconhecido'
        };
    }

    public static function getAccountType(int $type): string
    {
        return match ($type) {
            1 => 'Conta corrente',
            default => 'Conta poupança',
        };
    }
}
