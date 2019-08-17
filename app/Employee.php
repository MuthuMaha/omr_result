<?php

namespace App;
 
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
 use App\Token;
class Employee extends Authenticatable
{
    use Notifiable;
     protected $table='employees';

     protected $fillable = [
        'name', 'email', 'payroll_id','password','description',
    ];

   //   protected $table='t_employee';

   //   protected $fillable = [
   //     'EMPLOYEE_ID', 'SURNAME', 'NAME', 'DOB', 'DOJ', 'DOL', 'EMP_ID', 'USER_NAME', 'PASS_WORD', 'FMS_LOGIN', 'IP_ADDRESS', 'MAC_ADDRESS', 'LAST_LOGIN_TIME','MOBILENO', 'PAYROLL_ID', 'STATUS', 'CAMPUS_ID', 'ORG_ID', 'DESIGNATION', 'ROLE_ID', 'CREATED_ON', 'CREATED_BY', 'MOBILE', 'UUID', 'CASH_AMOUNT', 'DD_AMOUNT', 'CHEQUE_AMOUNT', 'SUBJECT', 'EMP_TYPE', 'IMAGE_PATH', 'ESI_ELIGIBLE', 'PF_ELIGIBLE', 'EMP_TRANSFER_STATUS', 'EMP_TRANSFER_CAMPUS', 'PRINTER_TYPE', 'REPORTER_ID', 'ALLOWED_CONCESSION', 'IS_VALID', 'GENDER', 'PAN', 'IS_COLLEGE', 'IS_DGM', 'ACCOUNT_NO', 'BANK_NAME', 'MOBILE_DEVICE_ID', 'ACCESS_STATUS', 'MANAGER_ID', 'PWD_STATUS', 'EMP_HISTORY_ID', 'INCENTIVE', 'COMPANY', 'PAYROLL_CAMPUS_ID', 'SAL_RANGE', 'PF_NUMBER', 'BUILDING_ID', 'REGISTRATION_TYPE', 'fcm_id', 'APP_ROLE_ID', 'SKIP_ATTENDANCE'
   // ];
    
    protected $hidden = [
        'password', 'remember_token',
    ];
      public function getDESIGNATIONAttribute($value)
    {
        return ucfirst(strtolower($value));
    }
     public function roles()
    {
        return $this->belongsToMany('App\role');
    }

   public function tokens () {
        return $this->hasMany(Token::class, 'user_id', 'id');
    }
     

}
