<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogActionHistory extends Model
{
    use HasFactory;

    protected $table = 'log_action_histories';

    protected $fillable = [
        'table_name',
        'user_id',
        'action',
        'row_id',
    ];
}
