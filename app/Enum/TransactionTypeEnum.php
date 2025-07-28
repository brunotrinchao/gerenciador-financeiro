<?php

namespace App\Enum;

enum TransactionTypeEnum: string
{
    case INCOME = 'INCOME';
    case EXPENSE = 'EXPENSE';
    case TRANSFER = 'TRANSFER';
}
