<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory;
    use SoftDeletes;

    const FLAT = 0;

    const SLIDE = 1;

    protected $table = 'client';

    protected $fillable = [
        'user_edit_id',
        'company_name',
        'represent_name',
        'email',
        'address',
        'presence',
        'license_number',
        'total_year',
        'contractor_id',
        'client_id',
        'service_name',
        'contract_method',
        'charge_fee_rate',
        'charge_fee_memo',
        'settlement_fee_rate',
        'settlement_fee_memo',
        'is_transfer_fee',
        'termination_date',
        'balance'
    ];

    public function client_details()
    {
        return $this->hasMany(ClientContractDetail::class, 'client_id', 'client_id');
    }

    public function contractor()
    {
        return $this->belongsTo(Contractor::class, 'contractor_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_edit_id', 'id');
    }

    public function account()
    {
        return $this->hasMany(Account::class, 'client_id', 'client_id');
    }

    public function expense()
    {
        return $this->hasMany(ExpenseInformation::class, 'client_id', 'id');
    }

    public function clientContractDetail()
    {
        return $this->hasMany(ClientContractDetail::class, 'client_id', 'client_id');
    }
}
