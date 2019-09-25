<?php

namespace App\OmrModels;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Auth;
use App\Http\Requests\LoginValidation;
use Carbon\Carbon;
use App\Employee;
use App\Http\Resources\GroupCollection;
use App\OmrModels\Parent_details;
use App\OmrModels\Fcmtoken;
use App\BaseModels\Student;
use App\BaseModels\Program;
use App\BaseModels\StudyClass;
use App\BaseModels\Campus;
use App\OmrModels\Tparent;
use \App\OmrModels\Version;
use App\Token;
use App\OmrModels\User;
use Illuminate\Http\Request;
use App\OmrModels\Exam;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\EmployeeCollection;
use App\Http\Resources\ExamCollection;
use DB;
use File;
use Illuminate\Database\Eloquent\Model;

class Result extends Authenticatable
{
   use Notifiable;
   public static function login($data){
    $start= memory_get_usage(false); 
    $start_time = microtime(true);
         $msg="This is old token";
         $campus="";
         $a=[1,2,3,4,56];
          $version=Version::orderby('version_number','DESC')->first();

         //Login with three driver for different login
        if($data->user_type=="employee" || $data->user_type=="director")
        {
        Auth::attempt([ 'PAYROLL_ID' => $data->get('USERNAME'), 'password' => '123456' ]);
        }
        if($data->user_type=="student")
        {
        Auth::guard('t_student')->attempt([ 'ADM_NO' => $data->get('USERNAME'), 'password' => '123456' ]);
        }
        if($data->user_type=="parent")
        {
        Auth::guard('tparent')->attempt([ 'ADM_NO' => $data->get('USERNAME'), 'password' => '123456' ]);
        }

        // dd(Auth::user()->PAYROLL_ID);
      if(isset(Auth::user()->PAYROLL_ID)|| Auth::guard('t_student')->id()|| Auth::guard('tparent')->id()){
         $c=array();
       self::notify($data->fcm_token,'Login Successfully',$data->USERNAME,$data->user_type);

            if(isset(Auth::user()->PAYROLL_ID)){
                $client = Employee::
                                join('t_campus as tc','t_employee.CAMPUS_ID','=','tc.CAMPUS_ID')
                                // ->join('t_employee as te','te.PAYROLL_ID','=','employees.payroll_id')
                              ->where('PAYROLL_ID',Auth::user()->PAYROLL_ID)->get()[0];
                 $uc=Token::whereUser_id(Auth::user()->PAYROLL_ID)->delete();
                $campus=$client->CAMPUS_NAME;
                $details=[
                    'USER_NAME'=>ucfirst(strtolower($client->USER_NAME)),
                    'CAMPUS_NAME'=>ucfirst(strtolower($campus)),
                    'SURNAME'=>ucfirst(strtolower($client->SURNAME)),
                    'NAME'=>ucfirst(strtolower($client->NAME)),
                    'USER'=>'EMPLOYEE',
                    'DEPARTMENT'=>Auth::user()->SUBJECT,
                    'DESIGNATION'=>Auth::user()->DESIGNATION,
                    'CAMPUS_ID'=>Auth::user()->CAMPUS_ID
                          ];
            
            $token=Token::whereUser_id(Auth::id())->pluck('access_token');
            $subject=DB::table('IP_Exam_Section as a')
                      ->join('0_subjects as b','a.SUBJECT_ID','b.SUBJECT_ID')
                      ->where('a.EMPLOYEE_ID',Auth::user()->PAYROLL_ID)
                      ->select('b.subject_id','b.subject_name')
                      ->distinct()
                      ->get(); 

               $end_time = microtime(true);
               $execution_time = ($end_time - $start_time);
               $end=memory_get_usage(false);
               $used_memory_bytes=$end-$start;
           if($uc){
             $msg='Token expired and New Token generated';
           }
            if (!$token->count()) {
                $str=str_random(10);
                $token=Token::create([
                    'user_id'=>Auth::user()->PAYROLL_ID,
                    'expiry_time'=>'1',
                    'access_token' => Hash::make($str),
                ]);
                if($data->user_type=='employee')
                    return [
                        'Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            'Token'=>$token->access_token,
              'Version'=>$version->version_number,
                            
                            ],
                            'Details'=>$details,
                            'used'=>Tparent::get_proper_format($used_memory_bytes),
                           
                            'peak'=>Tparent::get_proper_format(memory_get_peak_usage(true)),
                            'execution_time'=>$execution_time,
                            'Subject'=>$subject,
                    ];
                    else
                       return [
                        'Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            'Token'=>$token->access_token,
                            'Version'=>$version->version_number,
                            
                            ],
                            'Details'=>$details,
                            'used'=>Tparent::get_proper_format($used_memory_bytes),
                           
                            'peak'=>Tparent::get_proper_format(memory_get_peak_usage(true)),
                            'execution_time'=>$execution_time,
                            // 'Subject'=>$subject,
                            'Group'=>new GroupCollection(DB::table('t_course_group')->get())
                    ];

         
            }
        }
                  elseif(Auth::guard('t_student')->id())
                  {
           $campus=Campus::where('CAMPUS_ID',Auth::guard('t_student')->user()->CAMPUS_ID)->pluck('CAMPUS_NAME');
                $details=[
                    'NAME'=>ucfirst(strtolower(Auth::guard('t_student')->user()->NAME)),
                    'USER_NAME'=>ucfirst(strtolower(Auth::guard('t_student')->user()->USER_NAME)),
                    'SURNAME'=>ucfirst(strtolower(Auth::guard('t_student')->user()->SURNAME)),
                    'USER'=>'STUDENT',
                    'CAMPUS_NAME'=>ucfirst(strtolower($campus[0])),
                    'GROUP'=>Auth::guard('t_student')->user()->GROP,
                    // 'SUBJECT'=>Auth::guard('t_student')->user()->SUBJECT,
                    'PROGRAM_NAME'=>Program::where('PROGRAM_ID',Auth::guard('t_student')->user()->PROGRAM_ID)->pluck('PROGRAM_NAME')[0],
                    'CLASS_NAME'=>StudyClass::where('CLASS_ID',Auth::guard('t_student')->user()->CLASS_ID)->pluck('CLASS_NAME')[0],
                    'CAMPUS_ID'=>Auth::guard('t_student')->user()->CAMPUS_ID,
                    'ACADEMIC_YEAR'=>Auth::guard('t_student')->user()->ACADEMIC_YEAR,
                    'YEAR'=>Auth::guard('t_student')->user()->CLASS_ID
                          ];
                $uc=Token::whereUser_id(Auth::guard('t_student')->id())->delete();

                   $token=Token::whereUser_id(Auth::guard('t_student')->id())->pluck('access_token');
         
            if (!$token->count()) {
                $str=str_random(10);
                $token=Token::create([
                    'user_id'=>Auth::guard('t_student')->id(),
                    'expiry_time'=>'1',
                    'access_token' => Hash::make($str),
                ]);
   
               $end_time = microtime(true);
               $execution_time = ($end_time - $start_time);
               $end=memory_get_usage(false);
               $used_memory_bytes=$end-$start;
                    return [
                        'Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            'Token'=>$token->access_token,
                            'Version'=>$version->version_number,
                            
                            ],
                            'Details'=>$details,
                            'used'=>Tparent::get_proper_format($used_memory_bytes),
                           
                            'peak'=>Tparent::get_proper_format(memory_get_peak_usage(true)),
                            'execution_time'=>$execution_time,
                    ];
         
            }
           }
           else
           {
            // $student=DB::select('SELECT * FROM `t_parent_details` WHERE ADM_NO Like "%'.Auth::guard('tparent')->id().'" LIMIT 1');

            $campus=Campus::where('CAMPUS_ID',Auth::guard('tparent')->user()->CAMPUS_ID)->pluck('CAMPUS_NAME');
            if(count($campus)==0)
               return [
                        'Login' => [
                            'response_message'=>"error username or password wrong",
                            'response_code'=>"0"
                           ],
                    ];
             $details=[
                    'NAME'=>ucfirst(strtolower(Auth::guard('tparent')->user()->NAME)),
                    'USER'=>'PARENT',
                    'PROGRAM_NAME'=>Program::where('PROGRAM_ID',Auth::guard('tparent')->user()->PROGRAM_ID)->pluck('PROGRAM_NAME')[0],
                    'CLASS_NAME'=>StudyClass::where('CLASS_ID',Auth::guard('tparent')->user()->CLASS_ID)->pluck('CLASS_NAME')[0],
                    'CAMPUS_NAME'=>ucfirst(strtolower($campus[0])),
                    'STUDENT'=>ucfirst(strtolower(Auth::guard('tparent')->user()->NAME)),
                    'CAMPUS_ID'=>Auth::guard('tparent')->user()->CAMPUS_ID,
                    'YEAR'=>Auth::guard('tparent')->user()->CLASS_ID
                          ]; 
                $uc=Token::whereUser_id(Auth::guard('tparent')->id())->delete();

                   $token=Token::whereUser_id(Auth::guard('tparent')->id())->pluck('access_token');
               
            if (!$token->count()) {
                $str=str_random(10);
                $token=Token::create([
                    'user_id'=>Auth::guard('tparent')->id(),
                    'expiry_time'=>'1',
                    'access_token' => Hash::make($str),
                ]);

               $end_time = microtime(true);
               $execution_time = ($end_time - $start_time);
               $end=memory_get_usage(false);
               $used_memory_bytes=$end-$start;
                    return [
                        'Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            'Token'=>$token->access_token,
              'Version'=>$version->version_number,
                            
                            ],
                            'Details'=>$details,
                            'used'=>Tparent::get_proper_format($used_memory_bytes),
                           
                            'peak'=>Tparent::get_proper_format(memory_get_peak_usage(true)),
                            'execution_time'=>$execution_time,
                          
                    ];
         
            }
           }
           if(Auth::id()){
            $subject=DB::table('IP_Exam_Section as a')
                      ->join('0_subjects as b','a.SUBJECT_ID','b.SUBJECT_ID')
                      ->where('a.EMPLOYEE_ID',Auth::user()->PAYROLL_ID)
                      ->select('b.subject_id','b.subject_name')
                      ->distinct()                      
                      ->get();  

               $end_time = microtime(true);
               $execution_time = ($end_time - $start_time);
               $end=memory_get_usage(false);
               $used_memory_bytes=$end-$start;
                    return [
                        'Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                        'Token'=>$token[0],
              'Version'=>$version->version_number,
                        
                            ],
                        'Details'=>$details,
                            'used'=>Tparent::get_proper_format($used_memory_bytes),
                           
                            'peak'=>Tparent::get_proper_format(memory_get_peak_usage(true)),
                            'execution_time'=>$execution_time,
                        'Subject'=>$subject,
                    ];
                  }
                    else{
                         return [
                        'Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                        'Token'=>$token[0],
              'Version'=>$version->version_number,
                        
                            ],
                        'Details'=>$details, 
                            'used'=>Tparent::get_proper_format($used_memory_bytes),
                           
                            'peak'=>Tparent::get_proper_format(memory_get_peak_usage(true)),
                            'execution_time'=>$execution_time,
                    ];
                  }
        }
        else{
                return [
                        'Login' => [
                            'response_message'=>"error username or password wrong",
                            'response_code'=>"0"
                           ],
                    ];
        }

   }

   public static function notify($token, $title,$USERNAME,$user_type)
   {
    // $fcm="";
    // $notify="";
    //    $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
    //    $token=$token;
    //    $body='{"group_id": 5,"class_id": 1,"stream_id": 2,"program_id": 1,"subject_id": 3,"mode_id":2,"test_type":1,"exam_id":2,"STUD_ID":"178041343","section_id":"78322","page":1,"user_type":"employee","USERID":"AMP100050","date":"2018-11"}';
    //    $url='total_percentage';

    //    $notification = [
    //        'title' => $title,
    //        'parameter'=>$body,
    //        'url'=>$url,
    //        "notify_type"=>'Exam Created',
    //    ];
        // $se1=Sendnotifications::where('USERID',$USERNAME)->get();
       $check1=Fcmtoken::where('token',$token)->where('USERID',$USERNAME)->pluck('id');
       if(!isset($check1[0]))
      $fcm=Fcmtoken::updateorcreate(['token'=>$token],['token'=>$token,'USERID'=>$USERNAME,'user_type'=>$user_type]);

    //    $notify=Notifymessage::create($notification);

    //    if(isset($notify->id) && isset($fcm->id) && isset($se1->id)){
    //    $send=Sendnotifications::create([
    //     "notification_ids"=>$notify->id,
        
    //     "USERID"=>$USERNAME,
    //                                     ]);
    //    }
    //    else{
    //     $se=Sendnotifications::where('USERID',$USERNAME)->get();
    //        $send=Sendnotifications::where([
    //       "USERID"=>$se[0]->USERID,])->update([
    //       "notification_ids"=>$se[0]->notification_ids.','.$notify->id,
    //      ]
    //                                       );
    //      }

       
    //    $extraNotificationData = ["message" => $notification,"moredata" =>'dd'];

    //    $fcmNotification = [
    //        'to'        => $token,
    //        'data' => $notification
    //    ];

    //    $headers = [
    //        'Authorization: key=AAAAKOCFNDk:APA91bGymao4PPgiubS42HVwSF0Ifbvuz546g7SpN03dky2I2QEf0dm3_qfOMjeGDzy91zU_YNEFme7UwJsKQ8su5ShokzmNxxkQn_IXM6J92qtVcusy7Hp3HnhADYGs5qs3U9qsFJTD',
    //        'Content-Type: application/json'
    //    ];

    //    $ch = curl_init();
    //    curl_setopt($ch, CURLOPT_URL,$fcmUrl);
    //    curl_setopt($ch, CURLOPT_POST, true);
    //    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    //    curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
    //    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
    //    $result = curl_exec($ch);
    //    curl_close($ch);

    //    return $result;
    return true;
   }
   public static function modelist($data)
   {
    $res=DB::table('0_test_modes')->where('test_mode_name','<>','')->select('test_mode_name','test_mode_id')->get();
   return ['Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            ],
                 "Data"=>$res
                ];

   }
   public static function mdexamlist($data)
   {
    $r[0]="";
      $rg=$data->rank_generated;

    if(isset($data->date))
      $date=$data->date;
    else
      $date=date('Y-m');
    $c=DB::table('result_application_blockcount')->where('API','mdexamlist')->pluck('Block_Count')[0];
    $r=array();
    $res=DB::table('1_exam_admin_create_exam as e')
            ->select('sl','test_code',DB::raw("DATE_FORMAT(e.start_date,'%d-%m-%Y') as start_date"),'max_marks','mode');
            if(isset($rg))
            $res->where('result_generated1_no0',$rg);
            if(isset($data->mode_id))
            $res->where('mode',$data->mode_id);
          if(isset($data->test_type))
            $res->where('test_type',$data->test_type);
          
            $res->where('start_date','like',$date.'%');
           
            $res->orderBy('sl','DESC');
            $res=$res->paginate($c);
            foreach ($res as $key => $value) {
             $b=DB::table('0_test_modes')->where('test_mode_id',$value->mode)->get();
             $r[$key]=DB::table($b[0]->marks_upload_final_table_name)
                   ->where('test_code_sl_id',$value->sl)
                   ->select(DB::raw('SUM(TOTAL) as total,COUNT(sl) as count'))->get()[0];
              $r[$key]->max_marks=array_sum(explode(',',$value->max_marks));
              $r[$key]->total_marks=$r[$key]->max_marks*$r[$key]->count;
              if(isset($r[$key]->total) && isset($r[$key]->total_marks))
              $r[$key]->total_percentage=($r[$key]->total/$r[$key]->total_marks)*100;
            else
              $r[$key]->total_percentage=0;

            $r[$key]->sl=$value->sl;
            $r[$key]->test_code=$value->test_code;
            $r[$key]->start_date=$value->start_date;
            if($r[$key]->total_percentage<1)
              unset($r[$key]);

            }
            
$date=date('Y-M',strtotime($date));
   return ['Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            ],
                 "Data"=>array_values($r),
                 "Total_page"=>$res->lastPage(),
                 "Exam_date"=>$date,
                 "Block_Count"=>$c
                ];

   }
   
   public static function md_adroitlist($data)
   {
     $c=DB::table('result_application_blockcount')->where('API','md_adroitlist')->pluck('Block_Count')[0];
    
  //   if($data->page!=1)
  //   $page=($data->page-1)*10;
  // else
  //   $page=0;
    $res=Exam::where('sl',$data->exam_id)->select('sl','test_code','mode')->get();
    $res1=DB::table('0_test_modes')->where('test_mode_id',$res[0]->mode)->get();
    // $res2=DB::table($res1[0]->marks_upload_final_table_name)->orderBy('ALL_INDIA_RANK','ASC')->where('test_code_sl_id',$data->exam_id)->where('ALL_INDIA_RANK','>',$page)->limit('10')->get();
    $res2=DB::table($res1[0]->marks_upload_final_table_name.' as mp')
              ->join('scaitsqb.t_student_bio as s','s.ADM_NO','=','mp.STUD_ID')
              ->join('1_exam_admin_create_exam as e','e.sl','=','mp.test_code_sl_id')
              // ->join('t_campus as c','c.CAMPUS_ID','=',$res1[0]->marks_upload_final_table_name.'.this_college_id')
                ->where('test_code_sl_id',$data->exam_id);

    if(isset($data->CAMPUS_ID))
    $res2->where('this_college_id',$data->CAMPUS_ID);
    if(isset($data->group_id))
    $res2->where('GROUP_ID',$data->group_id);
    if(isset($data->class_id))
    $res2->where('CLASS_ID',$data->class_id);
    if(isset($data->stream_id))
    $res2->where('STREAM_ID',$data->stream_id);
    if(isset($data->program_id))
    $res2->where('PROGRAM_ID',$data->program_id);


    $res2->orderBy('ALL_INDIA_RANK','ASC');
    $res2->select('e.sl','test_code','TOTAL','mp.STUD_ID','mp.PROGRAM_RANK','mp.STREAM_RANK','mp.SEC_RANK','mp.CAMP_RANK','mp.CITY_RANK','mp.DISTRICT_RANK','mp.STATE_RANK','mp.ALL_INDIA_RANK','this_college_id','CAMPUS_NAME','NAME','max_marks');

    $res2=$res2->paginate($c);
    foreach ($res2 as $key => $value) {
      $m=array_sum(explode(',',$value->max_marks));
      $res2[$key]->total_percentage=number_format((float) (($value->TOTAL/$m)*100), '2', '.', '');
      $res2[$key]->DISTOTAL=$value->TOTAL.'/'.$m;
    }
    // $res2[]=[
    //                 'response_message'=>"success",
    //                 'response_code'=>"1",
    //                 ];
    // $res2['data']=array_values((array)$res2['data']);

     // return [,
$custom = collect(['Login' => [
                'response_message'=>"success",
                'response_code'=>"1",
                ]]);

$data = $custom->merge($res2);

// return response()->json($data);
    // return response()->json([
    // 'books' => $res2,
    // 'Login' => [
    //             'response_message'=>"success",
    //             'response_code'=>"1",
    //             ],
    //   ]);
    return $data;

   }
   
}
