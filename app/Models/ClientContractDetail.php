<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientContractDetail extends Model
{
    use HasFactory;

    protected $table = 'client_contract_detail';

    protected $fillable = [
        'client_id',
        'service_type',
        'contract_date',
        'contract_rate',
        'max_amount',
        'usage_fee_amount',
        'is_minimun_charge',
        'description'
    ];
}
