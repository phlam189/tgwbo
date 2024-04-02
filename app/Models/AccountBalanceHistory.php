<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountBalanceHistory extends Model
{
    use HasFactory;
    protected $table = 'account_balance_history';

    protected $fillable = [
        'id',
        'account_number',
        'date_history',
        'balance',
        'client_id',
    ];
}
