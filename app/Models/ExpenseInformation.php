<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseInformation extends Model
{
    use HasFactory;

    protected $table = 'expense_information';

    protected $fillable = [
        'user_edit_id',
        'account_id',
        'expense_name',
        'interest_rate',
        'memo',
        'client_id',
        'expense_date',
    ];

    public function client()
    {
        return $this->hasOne(Client::class, 'client_id', 'client_id');
    }
}
