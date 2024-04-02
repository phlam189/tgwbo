<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntroducerInformation extends Model
{
    use HasFactory;

    protected $table = 'introducer_infomation';

    protected $fillable = [
        'user_edit_id',
        'company_name',
        'representative_name',
        'email',
        'address',
        'contractor_id',
        'presence',
        'referral_classification',
        'referral_fee',
        'contract_date',
        'client_id',
        'account_contractor_id',
    ];

    public function contructor()
    {
        return $this->belongsTo(Contractor::class, 'contractor_id', 'id');
    }
}