<?php

namespace App\OmrModels;
use Auth;
use DB;
use App\Temployee;

use Illuminate\Database\Eloquent\Model;
include_once($_SERVER['DOCUMENT_ROOT'].'/sri_chaitanya/Exam_Admin/3_view_created_exam/z_ias_format.php');

class Subject extends Model
{
  protected $table='0_subjects';
  public $timestamps=false;
 public function getSubject_name($value)
    {
        return strtoupper($value);
    }
    public static function teacher_percentage($data,$change){
      $emp=Temployee::
                  join('t_campus as tc','tc.CAMPUS_ID','=','t_employee.CAMPUS_ID')
                  ->where('PAYROLL_ID',$data->USER_ID)
                  ->select('USER_NAME','DESIGNATION','PAYROLL_ID','tc.CAMPUS_ID','CAMPUS_NAME')
                  ->get();
      // return $emp;
        $studentlist=array();
        $studentlist1=array();
        $output1=array();
        $output=array();
        $examlist=array();
        $output2=array();
        $group_id=$data->group_id;
        $class_id=$data->class_id;
        $stream_id=$data->stream_id;
        $program_id=$data->program_id;
        $subject_id=$data->subject_id;
        $employee_id=Auth::user()->PAYROLL_ID;
        $campus_id=Auth::user()->CAMPUS_ID;
        if(isset($data->USER_ID)){
          // return $emp[0]->PAYROLL_ID;
        $employee_id=$emp[0]->PAYROLL_ID;
        $campus_id=$emp[0]->CAMPUS_ID;
        }
        $exam_id=$data->exam_id;
        $test_type=$data->test_type;
        $mode=$data->mode_id;
        if(isset($data->date))
        $date=$data->date;
        else
        $date=date("Y-m");
      if($change=="p")
        $date="";

        $subject_name=DB::table('0_subjects')->where('subject_id',$subject_id)->pluck('subject_name')[0];

        $section=DB::table('IP_Exam_Section')
                ->where('EMPLOYEE_ID',$employee_id)
                ->where('SUBJECT_ID',$subject_id)
                ->pluck('SECTION_ID');
                // return $section;

   if(isset($exam_id))
           $output=DB::table('1_exam_gcsp_id as eg')
                    ->join('1_exam_admin_create_exam as e','e.sl','=','eg.test_sl')
                    ->join('0_test_modes as tm','tm.test_mode_id','=','e.mode')
                    ->join('t_campus as tc','tc.STATE_ID','=','e.state_id')
                    ->join('t_employee as em','em.CAMPUS_ID','=','tc.CAMPUS_ID')
                    ->where('eg.GROUP_ID',$group_id)
                    ->where('eg.STREAM_ID',$stream_id)
                    ->where('eg.CLASS_ID',$class_id)
                    ->where('eg.PROGRAM_ID',$program_id)
                    ->where('e.result_generated1_no0',1)
                    ->where('em.CAMPUS_ID',$campus_id)
                    ->whereRaw('FIND_IN_SET(?,tm.test_mode_subjects)', [$subject_id])
                    ->where('eg.test_sl',$exam_id)
                    ->orderBy('eg.test_sl','desc')
                    ->select('eg.test_sl','tm.marks_upload_final_table_name','e.max_marks','e.model_year','e.paper','e.omr_scanning_type','tm.test_mode_name','tm.test_mode_id')
                    ->distinct()                    
                    ->get();
          else
        $output=DB::table('1_exam_gcsp_id as eg')
                    ->join('1_exam_admin_create_exam as e','e.sl','=','eg.test_sl')
                    ->join('0_test_modes as tm','tm.test_mode_id','=','e.mode')
                    ->join('t_campus as tc','tc.STATE_ID','=','e.state_id')
                    ->join('t_employee as em','em.CAMPUS_ID','=','tc.CAMPUS_ID')
                    ->where('eg.GROUP_ID',$group_id)
                    ->where('eg.STREAM_ID',$stream_id)
                    ->where('eg.CLASS_ID',$class_id)
                    ->where('eg.PROGRAM_ID',$program_id)
                    ->where('e.result_generated1_no0',1)
                    ->where('em.CAMPUS_ID',$campus_id)
                    ->orderBy('eg.test_sl','desc')                    
                    ->whereRaw('FIND_IN_SET(?,tm.test_mode_subjects)', [$subject_id])
                    ->select('eg.test_sl','tm.marks_upload_final_table_name','e.max_marks','e.model_year','e.paper','e.omr_scanning_type','tm.test_mode_name','tm.test_mode_id','tc.CAMPUS_ID')
                    ->distinct()
                    ->get();
 // return \Request::segment(2);
                    // return $output;
                  $page=$data->page;

                 if($change=="p"){
                  if(isset($data->exam_id))
                  return static::examstudent($output,$subject_name,$section,$data->exam_id,$data->section_id,$test_type,$mode,$date,$page,$campus_id,$emp)['Result'];  
                else
                   return static::examstudent($output,$subject_name,$section,"0",$data->section_id,$test_type,$mode,$date,$page,$campus_id,$emp)['Result'];  
                }
                 elseif($change=="e"){
                  $test=array();
                  $block_no=array();
                  $exam=static::examstudent($output,$subject_name,$section,$data->exam_id,$data->section_id,$test_type,$mode,$date,$page,$campus_id,$emp)['ExamList'];  
                  // return $exam;
                  // return [

                  //    'Login' => [
                  //           'response_message'=>"success",
                  //           'response_code'=>"1",
                  //           ],
                  //   "Data"=>$exam,
                  //       ];

                        $block_no=DB::table('Result_Application_BlockCount')->where('API','examlist')->pluck('Block_Count');
                  $max=$block_no[0]*$page;
                  $min=$max-$block_no[0];
                  for ($key=$min; $key <$max; $key++) 
                  {
                    if(isset($exam[$key])){
                    $value=$exam[$key];
                    if(isset($examlist['test_code']))
                    {                  
                      $test[]=$value->test_code;
                      $page=$data->page;
                      if(count($examlist['test_code'])!=$page)
                      {
                        if($mode==$value->test_mode_id && $test_type==$value->test_type_id)
                        {
                            $data1=new \stdClass(); 
                            $data1->exam_id=$value->sl;
                             if(isset(Type::teacher_exam_info($data1)['Total'])){
                            $examlist[$key]['test_code']=$value->test_code;
                            $examlist[$key]['Total_percentage']=Type::teacher_exam_info($data1)['Total'];
                           
                            // $examlist[$key]['Exam_Info']=Type::teacher_exam_info($data1);
                            //date("d-m-Y", strtotime($originalDate));
                            $examlist[$key]['test_sl']=$value->sl;
                            $examlist[$key]['start_date']=date("d-m-Y",strtotime($value->start_date));
                            $examlist[$key]['test_type_name']=$value->test_type_name;
                            $examlist[$key]['test_mode_name']=$value->test_mode_name;
                          }
                          }
                        }
                    }
                    else{
                         if($mode==$value->test_mode_id && $test_type==$value->test_type_id)
                          {
                            $data1=new \stdClass(); 
                            $data1->exam_id=$value->sl;
                            $data1->USER_ID=$data->USER_ID;
                            if(isset(Type::teacher_exam_info($data1)['Total'])){
                              // return "hi";
                            $examlist[$key]['test_code']=$value->test_code;
                            $examlist[$key]['Total_percentage']=Type::teacher_exam_info($data1)['Total'];
                           
                            // $examlist[$key]['Exam_Info']=Type::teacher_exam_info($data1);
                            
                            $examlist[$key]['test_sl']=$value->sl;
                            $examlist[$key]['start_date']=date("d-m-Y",strtotime($value->start_date));
                            $examlist[$key]['test_type_name']=$value->test_type_name;
                            $examlist[$key]['test_mode_name']=$value->test_mode_name;
                            }
                          }
                        }
                      }
                  }
                  if(empty($examlist))
                              return [
                                   'Login' => [
                                          'response_message'=>"success",
                                          'response_code'=>"1",
                                          ],
                                          "Exam_date"=>date('Y-M',strtotime($date)),
                                  "Totalpage"=>0,
                                  "Block_Count"=>0,
                                  "Exam"=>array(),
                                   ];
                  else
                  return [

                     'Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            ],
                    "Exam_date"=>date('Y-M',strtotime($date)),
                    "Totalpage"=>ceil((count($exam))/($block_no[0]+1)),
                    "Block_Count"=>$block_no[0],
                    "Exam"=>array_values($examlist),
                        ];
                  }               
                   elseif($change=="s"){ 
                  $student=static::examstudent($output,$subject_name,$section,$data->exam_id,$data->section_id,$test_type,$mode,$date,$page,$campus_id,$emp)['StudentList'];
                  // return $student;
                  if($student==0)
                     return [
                        'Login' => [
                            'response_message'=>"Exam_Id Required",
                            'response_code'=>"0"
                           ],
                    ]; 
                    if(count($student)==0)
                     return [
                        'Login' => [
                            'response_message'=>"Student Record Not found",
                            'response_code'=>"0"
                           ],
                    ];
                    if(!isset($student[0]))
                       return [
                        'Login' => [
                            'response_message'=>"Student Record Not found",
                            'response_code'=>"0"
                           ],
                    ];
                  $block_no=DB::table('Result_Application_BlockCount')->where('API','teacher_studentlist')->pluck('Block_Count');
                  $page=$data->page;
                  $max=$block_no[0]*$page;
                  $min=$max-$block_no[0];
                  $totalpage=count($student[0]['obtained'])/$block_no[0];
                  for ($j=0; $j<count($student); $j++) {
                  for ($i=$min; $i <$max; $i++) {
                    if(isset($student[$j]['obtained'][$i]->test_code)){

                      $studentlist1['max_marks']=$student[$j]['max_marks'];
                      $studentlist[$i]['start_date']=$student[$j]['obtained'][$i]->start_date;
                      $studentlist1['test_code']=$student[$j]['obtained'][$i]->test_code;
                      $studentlist1['section_name']=$student[$j]['obtained'][$i]->section_name;
                      $studentlist[$i]['STUD_ID']=$student[$j]['obtained'][$i]->STUD_ID;
                      $studentlist[$i]['STUD_NAME']=$student[$j]['obtained'][$i]->NAME;
                      $studentlist[$i]['PROGRAM_RANK']=$student[$j]['obtained'][$i]->PROGRAM_RANK;
                      $studentlist[$i]['STREAM_RANK']=$student[$j]['obtained'][$i]->STREAM_RANK;
                      $studentlist[$i]['SEC_RANK']=$student[$j]['obtained'][$i]->SEC_RANK;
                      $studentlist[$i]['CAMP_RANK']=$student[$j]['obtained'][$i]->CAMP_RANK;
                      $studentlist[$i]['CITY_RANK']=$student[$j]['obtained'][$i]->CITY_RANK;
                      $studentlist[$i]['DISTRICT_RANK']=$student[$j]['obtained'][$i]->DISTRICT_RANK;
                      $studentlist[$i]['STATE_RANK']=$student[$j]['obtained'][$i]->STATE_RANK;
                      $studentlist[$i]['ALL_INDIA_RANK']=$student[$j]['obtained'][$i]->ALL_INDIA_RANK;
                      $studentlist[$i]['CAMPUS_ID']=$student[$j]['obtained'][$i]->this_college_id;
                      $studentlist[$i]['TOTAL']=$student[$j]['obtained'][$i]->{strtoupper($subject_name)}.'/'.$student[$j]['max_marks'];
                      // $studentlist[$i][strtoupper($subject_name)]=$student[$j]['obtained'][$i]->{strtoupper($subject_name)};
                      

                    $data1=new \stdClass(); 
                    $data1->exam_id=$data->exam_id;
                    $data1->STUD_ID=$student[$j]['obtained'][$i]->STUD_ID;
                      // $studentlist[$i]['Exam_Info']=Modesyear::exam_info($data1,1);
                    }}
                  }
                  if(empty($studentlist1))
                    return [

                     'Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            ],
                            "Data"=>array(),
                            "Totalpage"=>0,
                            "Block_Count"=>0,
                          ];
                  return [

                     'Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            ],
                    "Totalpage"=>ceil($totalpage),
                    "Block_Count"=>$block_no[0],
                    // "Max_Marks"=>$studentlist1['max_marks'],
                    // "Start_Date"=>$studentlist1['start_date'],
                    // "Test_Code"=>$studentlist1['test_code'],
                    // "Section_Name"=>$studentlist1['section_name'],
                    "Data"=>array_values($studentlist)
                  ];
                }

    }
    public static function examstudent($output,$subject_name,$section,$exam_id,$section_id,$test_type,$mode,$date,$page,$campus_id,$emp)
    {
        $examlist=array();
        $studentlist=array();
            $result=array();
            foreach ($output as $key => $value) 
            {
           $correctans=Exam::join('0_test_types as ty','1_exam_admin_create_exam.test_type','=','ty.test_type_id')
                        ->join('0_test_modes as tm','tm.test_mode_id','1_exam_admin_create_exam.mode')
                         ->select('1_exam_admin_create_exam.model_year','1_exam_admin_create_exam.paper','1_exam_admin_create_exam.omr_scanning_type','1_exam_admin_create_exam.to_from_range','1_exam_admin_create_exam.subject_string_final','1_exam_admin_create_exam.sl','1_exam_admin_create_exam.test_code','tm.test_mode_id','ty.test_type_id','tm.test_mode_name','ty.test_type_name','1_exam_admin_create_exam.start_date','tm.marks_upload_final_table_name')
                        ;
                        if(isset($page))
                         $correctans->where('sl',$value->test_sl);

            if($test_type!="")
            $correctans->where('ty.test_type_id',$test_type);

            if($mode!="")
            $correctans->where('1_exam_admin_create_exam.mode',$mode);
            if(isset($date))
            $correctans->where('1_exam_admin_create_exam.start_date','like',$date.'%');

               $correctans=$correctans->get();
          if(!empty($correctans[0])){
            // return ['Result'=>['Login' => [
            //                 'response_message'=>"No record found for this information",
            //                 'response_code'=>"0",
            //                 ]],'ExamList'=>$examlist
            //                 ,'StudentList'=>['Login' => [
            //                 'response_message'=>"No record found for this information",
            //                 'response_code'=>"0",
            //                 ]],
            //               ];
               
               // if(isset($correctans))
           $examlist[]=$correctans[0];
         // else
         //  $examlist[]="";

           if($correctans[0]->omr_scanning_type=='advanced')
           {
             $filedata=ias_model_year_paper($correctans[0]->model_year,$correctans[0]->paper);    
           }
            else
            {
              $filedata[0]=$correctans[0]->subject_string_final;
              foreach (explode(',',$filedata[0]) as $keyu => $valueu) 
              {
                $arr[]=DB::table('0_subjects')->where('subject_id',$valueu)->pluck('subject_name')[0];
              }
            }

            $b=explode(",",$value->max_marks);
             $table=$correctans[0]->marks_upload_final_table_name;
            if(is_array($filedata[0])){
             
            $a=array_values(array_filter($filedata[0]));
            if(count($b)!=count($a))
                 return ['Result'=>['Login' => [
                            'response_message'=>"No record found for this information",
                            'response_code'=>"0",
                            ]],'ExamList'=>$examlist
                            ,'StudentList'=>['Login' => [
                            'response_message'=>"No record found for this information",
                            'response_code'=>"0",
                            ]],
                          ];
                          // return ['Result'=>$a];
            $max=array_combine((array)$a,$b);
            $list=$max[strtoupper($subject_name)];

              }
            else{
             

              foreach ($arr as $keyh => $valueh) {
                // $a[]=$arr[$keyh];
                if(isset($arr[$keyh]) && isset($b[0][$keyh]))
                $max[$arr[$keyh]]=$b[$keyh];
              // dd($b[2]);
              }
              // dd($b[0]['mathematics']);
               if(!isset($max[$subject_name]))
                  return ['Result'=>['Login' => [
                            'response_message'=>"No record found for this information",
                            'response_code'=>"0",
                            ]],'ExamList'=>$examlist
                            ,'StudentList'=>['Login' => [
                            'response_message'=>"No record found for this information",
                            'response_code'=>"0",
                            ]],
                          ];
            $list=$max[$subject_name];

            }
            $list1=strtoupper($subject_name);

            foreach ($section as $value1) {
             $section1[] = $value1;
                }
                if(empty($section1))
                  return ['Result'=>['Login' => [
                            'response_message'=>"No record found for this information",
                            'response_code'=>"0",
                            ]],'ExamList'=>$examlist
                            ,'StudentList'=>['Login' => [
                            'response_message'=>"No record found for this information",
                            'response_code'=>"0",
                            ]],
                          ];
                             // DB::enableQueryLog();
                          // dd($list);
              if($exam_id !="0")

            // $res=DB::select("select (".$list1."/".$list.")*100 as percentage,a.STUD_ID,test_code_sl_id,st.SECTION_ID,".$list1.",ts.section_name,test_code,start_date,PROGRAM_RANK,STREAM_RANK,SEC_RANK,CAMP_RANK,CITY_RANK,DISTRICT_RANK,STATE_RANK,ALL_INDIA_RANK,st.NAME,st.SURNAME,this_college_id from ".$table."  as `a` inner join `scaitsqb.t_student_bio` as `st` on `st`.`ADM_NO` = `a`.`STUD_ID` inner join t_college_section as ts on ts.SECTION_ID=st.SECTION_ID inner join 1_exam_admin_create_exam as ex on ex.sl=test_code_sl_id where `test_code_sl_id` = '".$exam_id."' and `st`.`SECTION_ID`='".$section_id."'"); 
          $res=DB::table($table.' as a')
           ->join(DB::raw('scaitsqb.t_student_bio AS st'),'st.ADM_NO','=','a.STUD_ID')
            ->join('1_exam_admin_create_exam as ex','ex.sl','=','test_code_sl_id')
            ->join('t_college_section as ts','ts.SECTION_ID','=','st.SECTION_ID')
          ->select(DB::raw("(".$list1."/".$list.")*100 as percentage,a.STUD_ID,a.test_code_sl_id,st.SECTION_ID,".$list1.",ts.section_name,test_code,start_date,a.PROGRAM_RANK,a.STREAM_RANK,a.SEC_RANK,a.CAMP_RANK,a.CITY_RANK,a.DISTRICT_RANK,a.STATE_RANK,a.ALL_INDIA_RANK,st.NAME,this_college_id"))
         ->where('test_code_sl_id',$exam_id)
          ->where('st.SECTION_ID',$section_id)
          ->where('st.CAMPUS_ID',$campus_id)
          ->get();
          else
          // $res=DB::select("select (".$list1."/".$list.")*100 as percentage,a.STUD_ID,test_code_sl_id,st.SECTION_ID,".$list1.",ts.section_name,test_code,start_date,PROGRAM_RANK,STREAM_RANK,SEC_RANK,CAMP_RANK,CITY_RANK,DISTRICT_RANK,STATE_RANK,ALL_INDIA_RANK,st.NAME,st.SURNAME,this_college_id from ".$table." as `a` inner join `scaitsqb.t_student_bio` as `st` on `st`.`ADM_NO` = `a`.`STUD_ID` inner join t_college_section as ts on ts.SECTION_ID=st.SECTION_ID inner join 1_exam_admin_create_exam as ex on ex.sl=test_code_sl_id where `test_code_sl_id` = '".$value->test_sl."' and `st`.`SECTION_ID` in (".implode(',',$section1).")");
         
          $res=DB::table($table.' as a')
           ->join(DB::raw('scaitsqb.t_student_bio AS st'),'st.ADM_NO','=','a.STUD_ID')
            ->join('1_exam_admin_create_exam as ex','ex.sl','=','test_code_sl_id')
            ->join('t_college_section as ts','ts.SECTION_ID','=','st.SECTION_ID')
          ->select(DB::raw("(".$list1."/".$list.")*100 as percentage,a.STUD_ID,a.test_code_sl_id,st.SECTION_ID,".$list1.",ts.section_name,test_code,start_date,a.PROGRAM_RANK,a.STREAM_RANK,a.SEC_RANK,a.CAMP_RANK,a.CITY_RANK,a.DISTRICT_RANK,a.STATE_RANK,a.ALL_INDIA_RANK,st.NAME,this_college_id"))
          ->where('test_code_sl_id',$value->test_sl)
          ->whereIn('st.SECTION_ID',$section1)
          ->where('st.CAMPUS_ID',$campus_id)
          ->get();
          // dd($res);
          // $query = DB::getQueryLog();
           $studentlist[$key]['obtained']=$res;
           $studentlist[$key]['max_marks']=$list;

// dd($res);
            $addition=0;
            foreach ($res as $key2 => $value2) {
                $addition+=$value2->percentage;
            }
            if(count($res))
                if(isset($result[$value->test_mode_name])){
                     $result[$value->test_mode_name]=($result[$value->test_mode_name]+($addition/count($res)))/2;
                     $results[$value->test_mode_name]=$value->test_mode_id;
                   }
                 else{
                     $result[$value->test_mode_name]=$addition/count($res);
                     $results[$value->test_mode_name]=$value->test_mode_id;
                   }
            }     
            $a=0; 
          }
          
            $final=array();     
            foreach ($result as $keyl => $valuel) {
              if($valuel!=0){
             $final[$a]['Mode_name']=$keyl; 
             $final[$a]['Mode_id']=$results[$keyl]; 
             $final[$a]['Percentage']=number_format((float) $valuel, '2', '.', ''); 
             $a++;
              }
            }
        return [
          "Result"=>['Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            ],
            "data"=>$final,
          ],
          "ExamList"=>array_values($examlist),
          "StudentList"=>$studentlist,
        ];       

    }
    public static function sectionlist($data)
    {

      // SELECT s.SECTION_ID,cs.section_name FROM IP_Exam_Section as s inner join t_college_section as cs on s.SECTION_ID=cs.SECTION_ID where s.EMPLOYEE_ID='VSP204421' ORDER BY (SELECT DISTINCT s.SECTION_ID from 101_mpc_marks as mm inner join scaitsqb.t_student_bio as st on st.ADM_NO=mm.STUD_ID where st.SECTION_ID=s.SECTION_ID ) DESC

      if($data->USER_ID)

      $result=DB::select("SELECT s.SECTION_ID,cs.section_name FROM IP_Exam_Section as s inner join t_college_section as cs on s.SECTION_ID=cs.SECTION_ID where s.EMPLOYEE_ID='".$data->USER_ID."' ORDER BY (SELECT DISTINCT s.SECTION_ID from 101_mpc_marks as mm inner join scaitsqb.t_student_bio as st on st.ADM_NO=mm.STUD_ID where st.SECTION_ID=s.SECTION_ID ) DESC");
      else

      $result=DB::select("SELECT s.SECTION_ID,cs.section_name FROM IP_Exam_Section as s inner join t_college_section as cs on s.SECTION_ID=cs.SECTION_ID where s.EMPLOYEE_ID='".Auth::user()->PAYROLL_ID."' ORDER BY (SELECT DISTINCT s.SECTION_ID from 101_mpc_marks as mm inner join scaitsqb.t_student_bio as st on st.ADM_NO=mm.STUD_ID where st.SECTION_ID=s.SECTION_ID ) DESC");
      // $result=DB::table('IP_Exam_Section as is')
      //               ->join('t_college_section as cs','is.SECTION_ID','=','cs.SECTION_ID')
      //               ->where('is.EMPLOYEE_ID',Auth::user()->payroll_id)
      //               ->select('is.SECTION_ID','cs.section_name')
      //               ->distinct('is.SECTION_ID','cs.section_name')
      //               ->get();

      return ['Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            ],
        'data'=>$result];

    }
    
}
