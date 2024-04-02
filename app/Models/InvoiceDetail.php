<?php

namespace App\Models;
// app/Models/InvoiceDetail.php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'type',
        'description',
        'rate',
        'number_transaction',
        'system_usage_fee',
        'total_amount',
    ];

    public function invoice(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
