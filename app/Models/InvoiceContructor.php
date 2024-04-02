<?php

namespace App\Models;
// app/Models/InvoiceDetail.php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceContructor extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'invoice_date',
        'note',
        'contructor_id',
        'number',
    ];
}
