<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'account';

    protected $fillable = [
        'contractor_id',
        'bank_id',
        'user_edit_id',
        'service_type',
        'category_name',
        'bank_name',
        'branch_name',
        'representative_account',
        'account_number',
        'account_holder',
        'commission_rate',
        'balance',
        'client_id',
        'branch_code'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }

    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }
}