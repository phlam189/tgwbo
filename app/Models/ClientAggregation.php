<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientAggregation extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'client_aggregation';

    public const DESPOSIT = 1;
    public const WITHDRAWAL = 2;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_id',
        'user_edit_id',
        'type',
        'date',
        'number_trans',
        'transfer_fee_different',
        'number_trans_other_bank',
        'amount',
        'payment_amount',
        'settlement_fee',
        'number_refunds',
        'type_refund',
        'refund_amount',
        'refund_fee',
        'system_usage_fee',
        'account_number',
        'account_balance',
        'memo',
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

    public function history()
    {
        return $this->hasMany(ChargeHistory::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class,'client_id', 'client_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class,'account_number', 'account_number');
    }


}
