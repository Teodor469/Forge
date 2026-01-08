<?php

namespace App;

enum WalletType: string
{
    case Savings = 'savings';
    case Checking = 'checking';
    case CreditCard = 'credit_card';
    case DebitCard = 'debit_card';
    case Investment = 'investment';
    case Cash = 'cash';
}
