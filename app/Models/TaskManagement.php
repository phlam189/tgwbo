<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskManagement extends Model
{
    use HasFactory;
    /**
     * The table associated with the model.
     *
     * @var string
     */
     protected $table = 'task_management';



     /**
      * The attributes that are mass assignable.
      *
      * @var array
      */
     protected $fillable = [
         'client_id',
         'task_name',
         'date_sync',
         'status',
         'count',
     ];



     /**
      * The attributes that should be mutated to dates.
      *
      * @var array
      */
     protected $dates = [
         'date_sync',
         'created_at',
         'updated_at',
     ];



     // Add relationships and other model methods as needed
}
