<?php 

namespace App;

use Illuminate\Foundation\Auth\AuthenticatesUsers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Session;
use DB;
use App\Token;
use App\Employee;

use Illuminate\Support\Facades\Hash;

trait AuthUser {
    use AuthenticatesUsers {
        AuthenticatesUsers::login as frameworkReplaceMe;
        AuthenticatesUsers::username as fusername;
    }
     public function username()
    {
        return 'PAYROLL_ID';
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);
$product = DB::select('SELECT roles.name FROM `roles` INNER Join usersroles on roles.id=usersroles.role_id inner join employees on employees.id=usersroles.user_id WHERE employees.payroll_id="'.$request->payroll_id.'"');
        Session::push('cart', $product);
         $user = \App\Employee::where('PAYROLL_ID', $request->payroll_id)
                  // ->where('PASS_WORD',md5($request->password))
                  ->first();
                  if($user){
                     Auth::login($user);
                 }
                 else{
                    Session::flush();
                    $errors = new \stdClass();
                    $errors->payroll_id="These credentials do not match our records.";

                    Session::push('error',$errors);
                 }
     return redirect('/home');
    }


}



?>