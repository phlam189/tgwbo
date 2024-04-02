<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;

    protected $table = 'bank';
    
    protected $fillable = [
        'user_edit_id',
        'bank_name',
        'client_withdrawal_fee_1',
        'client_withdrawal_fee_2',
        'contract_withdrawal_fee_1',
        'contract_withdrawal_fee_2',
        'bank_list_name',
        'bank_code',
        'difference_fee'
    ];
}