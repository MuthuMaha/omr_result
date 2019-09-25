<?php

namespace App\Http\Controllers\OmrControllers;
use DB;
use App\OmrModels\Result;
use App\OmrModels\Exam;
use App\OmrModels\Type;
use App\OmrModels\Modesyear;
use App\OmrModels\Subject;
use App\BaseModels\Student;
use App\BaseModels\Campus;
use App\BaseModels\Program;
use App\BaseModels\StudyClass;
use App\OmrModels\Fcmtoken;
use App\Http\Requests\LoginResult;
use App\Http\Requests\Totalpercentage;
use App\Http\Requests\Examlist;
use App\Employee;
use App\Apicache;
// use App\BaseModels\Student;
use App\OmrModels\Tparent;
use Auth;
use  File;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ResultController extends Controller
{
    public function login(LoginResult $request)
    {
    $res=Result::login($request);
      return $res;      
    }
    public function modelist(Request $request)
    {
    $res=Result::modelist($request);
      return $res;      
    }
    public function md_adroitlist(Request $request)
    {
    $res=Result::md_adroitlist($request);
      return $res;      
    }
    public function mdexamlist(Request $request)
    {
    $res=Result::mdexamlist($request);
      return $res;      
    }
    public function total_percentage(Request $request){
        if($request->user_type=='student' || $request->user_type=='parent' ){
        $res=Exam::type($request);
        $un=Auth::user()->ADM_NO;
         $st=Apicache::updateOrCreate([ 'USERNAME'=>$un,
            'user_type'=>$request->user_type],[
            'USERNAME'=>$un,
            'user_type'=>$request->user_type,
            'total_percentage'=>json_encode($res),
        ]);
        }
        else{
            $change="p";
        $res=Subject::teacher_percentage($request,$change);
        $un=Auth::user()->PAYROLL_ID;
         $st=Apicache::updateOrCreate([ 'USERNAME'=>$un,
            'user_type'=>$request->user_type],[
            'USERNAME'=>$un,
            'user_type'=>$request->user_type,
            'total_percentage'=>json_encode($res),
            'group_id'=>$request->group_id,
            'class_id'=>$request->class_id,
            'stream_id'=>$request->stream_id,
            'program_id'=>$request->program_id,
            'subject_id'=>$request->subject_id,
        ]);
        }
       
        return $res;
    }
    public function md_total_percentage(Request $request){
        // $url = route('mdt', ['STUD_ID' => '178041343','user_type' => 'student']);
        // return $url;
        if($request->user_type=='student' || $request->user_type=='parent' ){
        $res=Exam::type($request);
        }
        else{
            $change="p";
        $res=Subject::teacher_percentage($request,$change);
        }

        return $res;
    }
    public function test_type_list(Request $request){
        $res=Exam::test_type_list($request);
        return $res;
    }
    public function examlist(Request $request){
        if($request->user_type=='student'||$request->user_type=='parent')
        {
        $res=Exam::examlist($request);
         $un=Auth::user()->ADM_NO;
        //  $st=Apicache::updateOrCreate([ 'USERNAME'=>$un,
        //     'user_type'=>$request->user_type],[
        //     'USERNAME'=>$un,
        //     'user_type'=>$request->user_type,
        //     'examlist'=>json_encode($res),
        // ]);
        }
        else{
             $change="e";
        $res=Subject::teacher_percentage($request,$change);
        if($request->USER_ID)
            $un=$request->USER_ID;
        else
            $un=Auth::user()->PAYROLL_ID;
        //  $st=Apicache::updateOrCreate([ 'USERNAME'=>$un,
        //     'user_type'=>$request->user_type],[
        //     'USERNAME'=>$un,
        //     'user_type'=>$request->user_type,
        //     'examlist'=>json_encode($res),
        //     'group_id'=>$request->group_id,
        //     'class_id'=>$request->class_id,
        //     'stream_id'=>$request->stream_id,
        //     'program_id'=>$request->program_id,
        //     'subject_id'=>$request->subject_id,
        //     'page'=>$request->page,
        //     'mode_id'=>$request->mode_id,
        //     'test_type'=>$request->test_type,
        //     'date'=>$request->date,
        // ]);
            }
        return $res;
    }
    public function AnswerDetails(Request $request){
        $res=Exam::AnswerDetails($request);
        return $res;
    }
    public function exam_info(Request $request){
        if($request->user_type=="student" || $request->user_type=="parent")
        $res=Modesyear::exam_info($request,0);
        else
        $res=Type::teacher_exam_info($request);

        return $res;
    }
    public function teacher_studentlist(Request $request){
          $change="s";
        $res=Subject::teacher_percentage($request,$change);
        return $res;
    }
    public function sectionlist(Request $request){
        $res=Subject::sectionlist($request);
        return $res;
    }
    public function notifications(Request $request){
        $res=Fcmtoken::notifications($request);
        return $res;
    }
    public function subject(Request $request){
        if(isset(Exam::AnswerDetails($request)['subject_name']))
      $res=Exam::AnswerDetails($request)['subject_name'];
  if(isset(Exam::AnswerDetails($request)['subject_id']))
      $res1=Exam::AnswerDetails($request)['subject_id'];
  if(isset($res))
      foreach ($res as $key => $value) {
          $arr[$key]['subject_id']=$res1[$key];
          $arr[$key]['subject_name']=$value;
      }
if(isset($arr))
        // return $res;
        return ['Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            ],
                 "Data"=>$arr
                ];
                else
                    return ['Login' => [
                            'response_message'=>".iit/.dat file not uploaded yet",
                            'response_code'=>"1",
                            ],
                 "Data"=>array()
                ];

    }  
    public function sendmessage(Request $request){
        $res=Fcmtoken::sendmessage($request);
        return $res;

    }
    public function campus(Request $request){
        $res=Student::studlist($request);
        return ['Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            ],
                 "data"=>$res
                ];

    }
    public function campuslist(Request $request){
        $res=Campus::select("CAMPUS_ID","CAMPUS_NAME")->get();
        return ['Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            ],
                 "data"=>$res
                ];

    }
    public function employeelist(Request $request){
        $res=DB::table('t_employee')
        ->where('CAMPUS_ID',$request->CAMPUS_ID)
        ->where('NAME','<>','')
        ->select("PAYROLL_ID","NAME")->get();
        return ['Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            ],
                 "data"=>$res
                ];

    }
    public function employeecombination(Request $request)
    {
       
        $employee_id=$request->USER_ID;
        $campus_id=$request->CAMPUS_ID;
            $output=DB::table('ip_exam_section as ie')
                    ->join('t_employee as em','ie.EMPLOYEE_ID','=','em.PAYROLL_ID')
                    ->join('t_college_section as tc','ie.SECTION_ID','=','tc.SECTION_ID')
                    ->join('t_study_class as sc','sc.CLASS_ID','=','ie.CLASS_ID')
                    ->join('t_course_group as eg','eg.GROUP_ID','=','ie.GROUP_ID')
                    ->join('t_stream as st','st.STREAM_ID','=','ie.STREAM_ID')
                    ->join('t_program_name as pn','pn.PROGRAM_ID','=','ie.PROGRAM_ID')
                    ->join('0_subjects as sb','sb.subject_id','=','ie.subject_id')
                    ->select('eg.GROUP_NAME','st.STREAM_NAME','sc.CLASS_NAME','pn.PROGRAM_NAME','sb.subject_name','ie.GROUP_ID','ie.STREAM_ID','ie.CLASS_ID','ie.PROGRAM_ID','ie.subject_id',"PAYROLL_ID","NAME","DESIGNATION")->distinct();

                    if(isset($campus_id))
                    $output->where('em.CAMPUS_ID',$request->CAMPUS_ID);
                    if(isset($employee_id))
                    $output->where('ie.EMPLOYEE_ID',$request->USER_ID);
                    $output=$output->get();

                    foreach ($output as $key => $value) {
                      $outpu[$value->PAYROLL_ID]['NAME']=$value->NAME;
                      $outpu[$value->PAYROLL_ID]['PAYROLL_ID']=$value->PAYROLL_ID;
                      $outpu[$value->PAYROLL_ID]['DESIGNATION']=$value->DESIGNATION;
                      $outpu[$value->PAYROLL_ID]['Details'][]=$value;
                    }
                    // return $output;
                    if(isset($outpu))
        return ['Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            ],
                 "data"=>array_values($outpu)
                ];
                else
                    return ['Login'=>[
                        'response_message'=>"No data found",
                        'response_code'=>0
                    ],
                    'data'=>array()
                ];

    }
    public function details(Request $request)
    {
        if($request->user_type=='employee'){
             $client = Employee::
                                join('t_campus as tc','t_employee.CAMPUS_ID','=','tc.CAMPUS_ID')
                                // ->join('t_employee as te','te.PAYROLL_ID','=','employees.payroll_id')
                              ->where('t_employee.PAYROLL_ID',$request->USER_ID)->get();
            if(isset($client[0])){
                              // return $client;
                $campus=$client[0]->CAMPUS_NAME;

                $details=[
                    'USER_NAME'=>ucfirst(strtolower($client[0]->USER_NAME)),
                    'CAMPUS_NAME'=>ucfirst(strtolower($campus)),
                    'SURNAME'=>ucfirst(strtolower($client[0]->SURNAME)),
                    'NAME'=>ucfirst(strtolower($client[0]->NAME)),
                    'USER'=>'EMPLOYEE',
                    'DEPARTMENT'=>$client[0]->SUBJECT,
                    'DESIGNATION'=>$client[0]->DESIGNATION,
                    'CAMPUS_ID'=>$client[0]->CAMPUS_ID
                          ];
            return [
                        'Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            ],
                            'Details'=>$details
                        ];
                    }
                    else{
                        return  [
                        'Login' => [
                            'response_message'=>"No Record Found",
                            'response_code'=>"0",
                            ],];
                    }

        }
        elseif($request->user_type=='student'){
            $stud=Student::where('ADM_NO',$request->USER_ID)->get();
            if(isset($stud[0])){
            $campus=Campus::where('CAMPUS_ID',$stud[0]->CAMPUS_ID)->pluck('CAMPUS_NAME');
                $details=[
                    'NAME'=>ucfirst(strtolower($stud[0]->NAME)),
                    'USER_NAME'=>ucfirst(strtolower($stud[0]->USER_NAME)),
                    'SURNAME'=>ucfirst(strtolower($stud[0]->SURNAME)),
                    'USER'=>'STUDENT',
                    'CAMPUS_NAME'=>ucfirst(strtolower($campus[0])),
                    'GROUP'=>$stud[0]->GROP,
                    // 'SUBJECT'=>Auth::guard('t_student')->user()->SUBJECT,
                    'PROGRAM_NAME'=>Program::where('PROGRAM_ID',$stud[0]->PROGRAM_ID)->pluck('PROGRAM_NAME')[0],
                    'CLASS_NAME'=>StudyClass::where('CLASS_ID',$stud[0]->CLASS_ID)->pluck('CLASS_NAME')[0],
                    'CAMPUS_ID'=>$stud[0]->CAMPUS_ID,
                    'ACADEMIC_YEAR'=>$stud[0]->ACADEMIC_YEAR,
                    'YEAR'=>$stud[0]->CLASS_ID
                          ];
                          return [
                        'Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            ],
                            'Details'=>$details
                        ];
                    }
                    else{
                        return  [
                        'Login' => [
                            'response_message'=>"No Record Found",
                            'response_code'=>"0",
                            ],];
                    }
        }
        else{
            $stud=Student::where('ADM_NO',$request->USER_ID)->get();
            if(isset($stud[0])){
            $campus=Campus::where('CAMPUS_ID',$stud[0]->CAMPUS_ID)->pluck('CAMPUS_NAME');
            if(count($campus)==0)
               return [
                        'Login' => [
                            'response_message'=>"error username or password wrong",
                            'response_code'=>"0"
                           ],
                    ];
             $details=[
                    'NAME'=>ucfirst(strtolower($stud[0]->NAME)),
                    'USER'=>'PARENT',
                    'PROGRAM_NAME'=>Program::where('PROGRAM_ID',$stud[0]->PROGRAM_ID)->pluck('PROGRAM_NAME')[0],
                    'CLASS_NAME'=>StudyClass::where('CLASS_ID',$stud[0]->CLASS_ID)->pluck('CLASS_NAME')[0],
                    'CAMPUS_NAME'=>ucfirst(strtolower($campus[0])),
                    'STUDENT'=>ucfirst(strtolower($stud[0]->NAME)),
                    'CAMPUS_ID'=>$stud[0]->CAMPUS_ID,
                    'YEAR'=>$stud[0]->CLASS_ID
                          ]; 
                          return [
                        'Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            ],
                            'Details'=>$details
                        ];
                    }else{
                        return  [
                        'Login' => [
                            'response_message'=>"No Record Found",
                            'response_code'=>"0",
                            ],];
                    }
        }
        

    }
}
