<?php

namespace App\OmrModels;
 
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
 use App\OmrModels\Token;
class Tparent extends Authenticatable
{
    use Notifiable;
     protected $table='scaitsqb.t_student_bio';
     // protected $guard = 't_student_bio';
     protected $primaryKey='ADM_NO';
    public $timestamps=false;
     public function roles()
    {
        return $this->belongsToMany('App\OmrModels\role');
    }

   public function tokens() {
        return $this->hasMany(Token::class, 'user_id', 'ADM_NO');
    }
     

        public static function get_proper_format($bytes)
            {
                if ($bytes >= 1073741824)
                {
                    $bytes = number_format($bytes / 1073741824, 2) . ' GB';
                }
                elseif ($bytes >= 1048576)
                {
                    $bytes = number_format($bytes / 1048576, 2) . ' MB';
                }
                elseif ($bytes >= 1024)
                {
                    $bytes = number_format($bytes / 1024, 2) . ' KB';
                }
                elseif ($bytes > 1)
                {
                    $bytes = $bytes . ' bytes';
                }
                elseif ($bytes == 1)
                {
                    $bytes = $bytes . ' byte';
                }
                else
                {
                    $bytes = '0 bytes';
                }

                return $bytes;
        }
}
