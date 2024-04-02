<?php

namespace App\Models;
// app/Models/Invoice.php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    protected $table = 'invoice';

    protected $fillable = [
        'client_id',
        'contractor_id',
        'invoice_no',
        'invoice_date',
        'due_date',
        'sub_total',
        'discount_amount',
        'tax_rate',
        'total_tax',
        'balance',
        'status',
        'memo',
        'period_from',
        'period_to',
        'count',
    ];

    public function invoiceDetails(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InvoiceDetail::class, 'invoice_id', 'id');
    }

    public function contrustor(): \Illuminate\Database\Eloquent\Relations\hasOne
    {
        return $this->hasOne(Contractor::class, 'id', 'contractor_id');
    }

    public function client(): \Illuminate\Database\Eloquent\Relations\hasOne
    {
        return $this->hasOne(Client::class, 'client_id', 'client_id');
    }
}
