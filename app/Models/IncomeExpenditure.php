<?php

namespace App\Models;
// app/Models/Invoice.php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomeExpenditure extends Model
{
    use HasFactory;
    protected $table = 'income_expenditure';

    protected $fillable = [
        'from_date',
        'to_date',
        'total_balance',
        'total_spending',
        'profit',
        'profit_wm',
        'profit_include_wm',
    ];

    public function incomeExpenditureDetails(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(IncomeExpenditureDetail::class, 'income_expenditure_id', 'id');
    }
}
