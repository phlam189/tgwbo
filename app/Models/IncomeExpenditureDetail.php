<?php

namespace App\Models;
// app/Models/InvoiceDetail.php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomeExpenditureDetail extends Model
{
    use HasFactory;
    protected $table = 'income_expenditure_detail';

    protected $fillable = [
        'income_expenditure_id',
        'client_id',
        'item_name',
        'type',
        'type_fee',
        'description',
        'rate',
        'number_transaction',
        'amount',
        'is_manual',
        'profit',
        'memo',
        'previous_month',
        'payment_status',
        'classification'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'date',
        'created_at',
        'updated_at',
    ];

    public function income(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(IncomeExpenditure::class);
    }
}
