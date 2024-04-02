<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contractor extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'contructor';

    protected $fillable = [
        'user_edit_id',
        'company_name',
        'manager',
        'email',
        'address',
        'invoice_prefix',
        'company_type',
        'representative_name',
        'date_of_birth',
        'contract_date',
        'presence',
        'existence',
        'parent_id',
        'is_honsha'
    ];

    public function children()
    {
        return $this->hasMany(Contractor::class, 'parent_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(Contractor::class, 'parent_id');
    }
}
