<?php

namespace App\Helpers;

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
}
