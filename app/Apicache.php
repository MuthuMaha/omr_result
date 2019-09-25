<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Apicache extends Model
{
	protected $table='apicaches';
    protected $fillable=['USERNAME', 'user_type',  'total_percentage', 'group_id', 'class_id', 'stream_id', 'program_id', 'subject_id','page', 'mode_id', 'test_type', 'date'];
}
