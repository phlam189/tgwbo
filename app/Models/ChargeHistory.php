<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChargeHistory extends Model
{
    use HasFactory;

    public const SETTLEMENT = 0;
    public const REFUND = 1;
    public const TRANSFER = 2;
    public const CHARGE = 3;
    public const INTEREST = 4;
    public const MISC = 5;
    public const BORROWING = 6;
    public const REPAYMENT = 7;
    public const DEPOSIT_WITHDRAWAL = 8;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'charge_history';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_id',
        'type',
        'payment_amount',
        'transfer_amount',
        'charge_fee',
        'memo',
        'memo_internal',
        'create_date',
        'type_client_aggregation',
        'account_number'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'create_date',
        'created_at',
        'updated_at',
    ];

    // Add relationships and other model methods as needed
}
