<?php

namespace App\OmrModels;
use App\BaseModels\Campus;
use App\BaseModels\Student;
use Auth;
use App\OmrModels\main;
use File;
use Illuminate\Http\Request;
use DB;
use Illuminate\Database\Eloquent\Model;
include_once($_SERVER['DOCUMENT_ROOT'].'/sri_chaitanya/Exam_Admin/3_view_created_exam/z_ias_format.php');
class Exam extends Model
{
  protected $table='1_exam_admin_create_exam';
  protected $primaryKey='sl';
  public $timestamps=false;
  public static function total($data){
    $start= memory_get_usage(false); 
    $start_time = microtime(true);
$stud1=Student::where('ADM_NO',$data->USER_ID)->select('ADM_NO','CAMPUS_ID','CAMPUS_NAME','PROGRAM_NAME','GROP','NAME')->get();
if(isset($stud[0])){
$ADM_NO=$stud1[0]->ADM_NO;
$CAMPUS_ID=$stud1[0]->CAMPUS_ID;
}
     if(isset($data->date))
        $date=$data->date;
        else
          $date=date("Y-m");
    $res_key=array();
    $mode=array();
    $calculation="";
    $marklist=array(); 
    $stud=Campus::whereRaw('CAMPUS_ID ='.Auth::user()->CAMPUS_ID)->select('STATE_ID')
            ->get();
    if($stud[0]=='' && $CAMPUS_ID!="")
       $stud=Campus::whereRaw('CAMPUS_ID ='.$CAMPUS_ID)->select('STATE_ID')
            ->get();
            //List the exam which is based on STATE_ID of the student
    $exam=static::whereRaw('FIND_IN_SET(?,state_id)', $stud[0]->STATE_ID)
          ->whereRaw('result_generated1_no0 =1')                
          ->select('mode','rank_generated_type','max_marks','sl','test_code','model_year','paper','omr_scanning_type','subject_string_final')
          ;
          //Validate test_type_id is set otherwise set as 1
    if(isset($data->test_type))
      $exam->where('test_type',$data->test_type);
    else
      $exam->where('test_type','1');

    if(isset($data->mode_id))
      $exam->where('mode',$data->mode_id);
    // if(isset($data->date))
    //   $exam->where('start_date','like',$data->date.'%');
    // else
      // $exam->where('start_date','like',$date.'%');
    $exam=$exam->orderBy('sl','desc')->get();

    foreach ($exam as $key => $value) 
    {
      //Fetch 0_test_modes details for table name
      $subject_marks=Mode::whereRaw('test_mode_id ='.$value->mode)
                  ->get();
      //Fetch record from that table name
      $exam_data=DB::table($subject_marks[0]->marks_upload_final_table_name)
      ->join('1_exam_admin_create_exam as e','e.sl','=',$subject_marks[0]->marks_upload_final_table_name.'.test_code_sl_id')
            ->whereRaw('test_code_sl_id ="'.$value->sl.'"')
          

            ->select('test_code_sl_id','STUD_ID','TOTAL','PROGRAM_RANK','STREAM_RANK','SEC_RANK','CAMP_RANK','CITY_RANK','DISTRICT_RANK','STATE_RANK','ALL_INDIA_RANK',DB::raw("DATE_FORMAT(e.start_date,'%d-%m-%Y') as start_date"),'e.test_code','e.max_marks');
            if(isset($data->USER_ID))
            $exam_data->whereRaw('STUD_ID ="'.$data->USER_ID.'"');
            else
            $exam_data->whereRaw('STUD_ID ="'.Auth::id().'"');
            // $exam_data->whereRaw('STUD_ID ="178041343"');

            if(\Request::segment(2)=="examlist")
             $exam_data->where('start_date','like',$date.'%');

            $exam_data=$exam_data->orderBy('test_code_sl_id','desc')->get();
            foreach ($exam_data as $keya => $valuea) {
              $exam_data[$keya]->DISTOTAL=(int)$valuea->TOTAL."/".array_sum(explode(',',$valuea->max_marks));
            }
      //Add max marks and test_mode_name for calculation
        if(isset($exam_data[0])){

          $marklist[]=$exam_data[0];
      $calculation=static::overallmarklist1(array_sum(explode(',',$value->max_marks)),$exam_data[0]->TOTAL); 
        if(array_key_exists($subject_marks[0]->test_mode_name, $mode)){
          $sum=$mode[$subject_marks[0]->test_mode_name]+$calculation;
          $mode[$subject_marks[0]->test_mode_name]=$sum/2;
          $modeid['test_mode_id'][$subject_marks[0]->test_mode_name]=$subject_marks[0]->test_mode_id;
        }
        else{
        $mode[$subject_marks[0]->test_mode_name]=$calculation;
        $modeid['test_mode_id'][$subject_marks[0]->test_mode_name]=$subject_marks[0]->test_mode_id;

        }       
      } 
    }

    $a=0;
    foreach($mode as $key=>$value){
        $res_key[$a]["Mode_name"] = $key;
        $res_key[$a][ "Percentage"] = number_format((float) $value, '2', '.', '');
        $res_key[$a][ "Mode_id"] = $modeid['test_mode_id'][$key];
        $a++;
        }

               $end_time = microtime(true);
               $execution_time = ($end_time - $start_time);
               $end=memory_get_usage(false);
               $used_memory_bytes=$end-$start;
        if(empty($res_key))
          return [
                        'Mode' =>['Login'=> [
                            'response_message'=>"No Record Found",
                            'response_code'=>"1"
                           ],"data"=>array(), "Exam_date"=>date('Y-M',strtotime($date))],
                             'Marklist' =>['Login'=> [
                            'response_message'=>"Exam List Not Found",
                            'response_code'=>"1"
                           ],"data"=>array(), "Exam_date"=>date('Y-M',strtotime($date))],
                    ];
    return [
        "Mode"=>['Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            ],
                            "data"=>$res_key
                            , "Exam_date"=>date('Y-M',strtotime($date)),
                            'used'=>Tparent::get_proper_format($used_memory_bytes),
                           
                            'peak'=>Tparent::get_proper_format(memory_get_peak_usage(true)),
                            'execution_time'=>$execution_time,
                          ],
        "Marklist"=>['Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            ],"data"=>$marklist,
                              "Exam_date"=>date('Y-M',strtotime($date)),
                            'used'=>Tparent::get_proper_format($used_memory_bytes),
                           
                            'peak'=>Tparent::get_proper_format(memory_get_peak_usage(true)),
                            'execution_time'=>$execution_time,],
        ];
    
  }
  //Calculation for individual subject with maxmark
  public static function overallmarklist($data){
    //This function is not finalised yet
    foreach ($data as $key => $value) {
      for($i=0;$i<=count($value->max_marks)-1;$i++)
      {
        if($value->omr_scanning_type=="advanced"){
          $subject_name=Subject::where('subject_name','LIKE',$value->subject_string_final[$i]."%")
                ->get();
              }
        else{
          $subject_name=Subject::where('subject_id',$value->subject_string_final[$i])
                ->get();
        }
        $percentage[$i]=($value->{strtoupper($subject_name[0]->subject_name)}/$value->max_marks[$i])*100;
      }
      $sum = array_sum($percentage)/count($percentage);
      return $sum;
    }
  }
  //Caluculate overall mark with sum ofmax_mark 
  public static function overallmarklist1($max_marks,$total){
      if($max_marks)
      return ($total/$max_marks)*100;

  }
  public static function type($data){
    $out=static::total($data)['Mode'];
    return $out;
  }
  public static function examlist($data){
    $out=static::total($data)['Marklist'];
    return $out;
  }
  public static function test_type_list($data){
if(isset($data->group_id))
    $out=DB::select("
SELECT * FROM `0_test_types` as tt where `test_type_id` <> 0 ORDER BY test_type_id=1 DESC, (SELECT count(ee.sl) FROM `1_exam_admin_create_exam` as ee inner join 1_exam_gcsp_id as ei on ei.test_sl=ee.sl WHERE ee.`result_generated1_no0`=1 and ee.`test_type`=tt.`test_type_id` and GROUP_ID='".$data->group_id."' and CLASS_ID='".$data->class_id."' and STREAM_ID='".$data->stream_id."' and PROGRAM_ID='".$data->program_id."') DESC");
else
    $out=DB::select("
SELECT * FROM `0_test_types` as tt where `test_type_id` <> 0 ORDER BY test_type_id=1 DESC, (SELECT count(ee.sl) FROM `1_exam_admin_create_exam` as ee inner join 1_exam_gcsp_id as ei on ei.test_sl=ee.sl WHERE ee.`result_generated1_no0`=1 and ee.`test_type`=tt.`test_type_id`) DESC");
    return 
                     ['Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            ],"data"=>$out];
  }
  public static function AnswerDetails($data){
    // return $data;
    $smark=$data;
    if(!isset($data->subject_id)){
      
      $data->subject_id=1;
    }
    $type="";
   $correctans=static::where('sl',$data->exam_id)->select('key_answer_file_long_string as CorrectAnswer','model_year','paper','omr_scanning_type','to_from_range','subject_string_final','sl','test_code','mode')->get();
   if(sizeof($correctans)==0)
    return  [
              'Login' => [
                            'response_message'=>"Send correct exam_id",
                            'response_code'=>"0",
                            ]
            ];

   if($correctans[0]->omr_scanning_type=='advanced')
   {
    $result_string=Mode::where('test_mode_id',$correctans[0]->mode)->pluck('marks_upload_final_table_name')[0];
    $Result=DB::table($result_string)->where('test_code_sl_id',$data->exam_id)->where('STUD_ID',Auth::user()->ADM_NO)->pluck('Result_String');
    if(!isset($Result[0]))
      $Result=DB::table($result_string)->where('test_code_sl_id',$data->exam_id)->where('STUD_ID',$data->STUD_ID)->pluck('Result_String');
     $filedata=ias_model_year_paper($correctans[0]->model_year,$correctans[0]->paper);
     $marked=main::AnswerObtain($data,$correctans,array_filter($filedata[1]));
     // return $filedata[0];
     return static::AdvanceAnswer($filedata,$correctans,$marked,$data->subject_id,$Result[0],$result_string,$data->exam_id,$data->STUD_ID,$smark);
    }
    else
    {
      if(isset($correctans[0]))
      $result_string=Mode::where('test_mode_id',$correctans[0]->mode)->pluck('marks_upload_final_table_name')[0];
    
    if(isset($data->STUD_ID))
      $Result=DB::table($result_string)->where('test_code_sl_id',$data->exam_id)->where('STUD_ID',$data->STUD_ID)->pluck('Result_String');
    else
    $Result=DB::table($result_string)->where('test_code_sl_id',$data->exam_id)->where('STUD_ID',Auth::user()->ADM_NO)->pluck('Result_String');
     $marked=main::AnswerObtain($data,$correctans,$type);
     if(sizeof($marked)==0)
       return  [
              'Login' => [
                            'response_message'=>"Send correct exam_id",
                            'response_code'=>"0",
                            ]
            ];
      $subj=array();

      $filedata[6]=$correctans[0]->to_from_range;

      foreach (explode(',',$correctans[0]->subject_string_final) as $key => $value) 
      {
        $subj[]=DB::table('0_subjects')->where('subject_id',$value)->pluck('subject_name')[0];
      }
      $filedata[0]=$subj;
      $filedata[9]=explode(',',$correctans[0]->subject_string_final);
    if(sizeof($Result)==0)
     return  [
              'Login' => [
                            'response_message'=>"No data found",
                            'response_code'=>"0",
                            ]
            ];

       return static::NonAdvanceAnswer($filedata,$correctans,$marked,$data->subject_id,$Result[0],$result_string,$data->exam_id,$data->STUD_ID,$smark);
    }

  }
  public static function NonAdvanceAnswer($data,$ans,$marked,$id,$result,$table,$exam_id,$STUD_ID,$smark){
    $ob=array();
    $start= memory_get_usage(false); 
    $start_time = microtime(true);
    $markf=Modesyear::exam_info($smark,0)['CorrectMark'];

    $result=str_split($result);
    // return $result;
    $sl=$ans[0]->sl;
    $test_code=$ans[0]->test_code;
   $list=array();
   $correct=explode(',', $ans[0]->CorrectAnswer);
    $b1=explode(',', $data[6]);
    $b2=end($b1);
    $b3=explode('-', $b2);

    $i=1;    $s=0;    $su=0;    $sub=0;    $ans=0;

    $subject_list=explode(',', $data[6]);

    $subject_name=array_filter($data[0]);

    $temp="";
    if(count($marked['ansdata'])==0)
      return ['Login' => [
                            'response_message'=>"No record found",
                            'response_code'=>"0",
                            ],
                            'data'=>array()];
    
    for ($key=0; $key<=end($b3)-1; $key++) 
    { 
      $subjectwise=explode('-',$subject_list[$su]);

        $subject=$subject_name[$sub];

      if($key==end($subjectwise)-1)
      {
        $su++;        $sub++;
      }
      // $list['Exam_Id']=$sl;
      // $list['Exam_Name']=$test_code;
      // dd($ans);
      // $list['Subject'][$subject][$i]= new \stdClass();
       if(isset($marked['ansdata'][$ans])){
      $list[$subject][$i]['question_no']=$i;
      // $list[$subject][$i]->{'subject_name'}=$subject;
      $list[$subject][$i]['question_type']="";
      if(isset($markf[$i-1]))
      $list[$subject][$i]['obtained_mark']=$markf[$i-1];

      $list[$subject][$i]['correct_answer']=$correct[$ans];
      if(isset($marked['ansdata'][$ans]))
      $list[$subject][$i]['marked_answer']=$marked['ansdata'][$ans];
       // $list['Number_of_Subjects']=count($subject_name);
      if(isset($result[$i-1]))
       $list[$subject][$i]['result_string']=$result[$i-1];
   }

      $i++;
      $ans++;
    }
     $sub=Subject::where('subject_id',$id)->pluck('subject_name')[0];
      $new=array();
      if(isset($list[$sub]))
      for ($i=1; $i <=1 ; $i++) 
      {
            $ob=DB::table($table)
                  ->join('1_exam_admin_create_exam as e','e.sl',$table.'.test_code_sl_id')
                  ->where('test_code_sl_id',$exam_id)
                  ->where('STUD_ID',Auth::id())
                  ->select(strtoupper($sub),'max_marks')->get();
        if(!isset($ob[0]))
          $ob=DB::table($table)
                  ->join('1_exam_admin_create_exam as e','e.sl',$table.'.test_code_sl_id')        
                  ->where('test_code_sl_id',$exam_id)
                  ->where('STUD_ID',$STUD_ID)
                  ->select(strtoupper($sub),'max_marks')->get();
        $new[$i]['Section']='Section'.$i;
        $new[$i]['question_type']="";
        $new[$i]['Answer']=array_values($list[$sub]);
      }
      $new=array_values($new);

      $new=array_values($new);
      if(isset($ob[0])){
      $dr=array_combine($subject_name, explode(',',$ob[0]->max_marks));
      // $sb=array_merge($ans[0]->subject_string_final,$subject_name);
      $subject_name=array_flip($subject_name);
      $subject_name=array_change_key_case($subject_name,CASE_UPPER);

               $end_time = microtime(true);
               $execution_time = ($end_time - $start_time);
               $end=memory_get_usage(false);
               $used_memory_bytes=$end-$start;
    return 
                     ['Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            ],"Data"=>$new,
                            "subject_id"=>$data[9],
                            "subject_name"=>array_flip($subject_name),
                            "mark_obtained"=>$ob[0]->{strtoupper($sub)}."/".$dr[$sub],
                            'used'=>Tparent::get_proper_format($used_memory_bytes),
                           
                            'peak'=>Tparent::get_proper_format(memory_get_peak_usage(true)),
                            'execution_time'=>$execution_time,
                          ];
                        }
                        else{
                          return ['Login' => [
                            'response_message'=>"No record found",
                            'response_code'=>"0",
                            ],
                            'data'=>array()];
                        }
  }
  public static function AdvanceAnswer($data,$ans,$marked,$id,$result,$table,$exam_id,$STUD_ID,$smark){
    // return $data[1];
    $start= memory_get_usage(false); 
    $start_time = microtime(true);
    $markf=Modesyear::exam_info($smark,0)['CorrectMark'];
    $sbi=array();
    $a=0;
    $sl=$ans[0]->sl;
    $test_code=$ans[0]->test_code;
    $list=array();

    $correct=explode(',', $ans[0]->CorrectAnswer);

    $i=1;    $s=0;    $su=0;    $sub=1;    $ans=0;

    $subject_list=explode(',', $data[6]);
    $subject_name=array_filter($data[0]);
    $section_list=array_filter($data[1]);
    $result_list=str_split($result);
    $qno=array_filter($data[4]);
    $se_count=array_unique($section_list);
    // return $se_count;
    $temp="";
    if(count($marked['ansdata'])==0)
      return ['Login' => [
                            'response_message'=>"No record found",
                            'response_code'=>"0",
                            ],
                            'data'=>array(),];
    $ch=0;
    foreach ($section_list as $key => $value) 
    {
      $subjectwise=explode('-',$subject_list[$su]);
        $subject=$subject_name[$sub];
        $subject_id=Subject::where('subject_name',$subject_name[$sub])->pluck('subject_id')[0];
      if($key==end($subjectwise))
      {
        $sbi[]=$subject_id;
        $su++;        $sub++;
      }
      if($temp!=$value)
      {
        $temp=$value;
        $s++;
      }
      if($s>count($se_count))
        $s=1;
      // $list['Exam_Id']=$sl;
      // $list['Exam_Name']=$test_code;
      //  $list['Number_of_Subjects']=count($subject_name);
       // $list[]['Subject']=$subject;
       // $list[$subject]['Section'.$s]='Section'.$s;
   // return $markf;
       if(isset($correct[$ans])){
       $list[$subject]['Section'.$s][$key]['question_no']=$qno[$key-1];
       // if(isset($qno[$key]))
       $list[$subject]['Section'.$s][$key]['question_type']=$value;
       $list[$subject]['Section'.$s][$key]['obtained_mark']=$markf[$key-1];
       $list[$subject]['Section'.$s][$key]['correct_answer']=static::orcondition($correct[$ans]);
       if(isset($marked['ansdata'][$ans]))
       $list[$subject]['Section'.$s][$key]['marked_answer']=$marked['ansdata'][$ans];
       $list[$subject]['Section'.$s][$key]['result_string']=$result_list[$key-1];
       }// $list[$s]['Section'][]='Section'.$s;
       $ch++;
       // $list['data'][]='Section'.$s;
      // $list[$s]['data'][][$key]['question_no']=$key;
      // $list[$s]['data'][][$key]['question_type']=$value;
      // $list[$s]['data'][][$key]['correct_answer']=static::orcondition($correct[$ans]);
      // $list[$s]['data'][][$key]['marked_answer']=$marked['ansdata'][$ans];
 
      // $list['Subject'][$subject.'_Section'.$s][]= new \stdClass();
       // $list['Subject'][$subject.'_Section'.$s]=array();
      // $list['Subject'][$subject][$i]['question_no']=$key;
      // $list['Subject'][$subject][$i]['section_name']='Section'.$s;
      // $list['Subject'][$subject][$i]['question_type']=$value;
      // $list['Subject'][$subject][$i]['correct_answer']=static::orcondition($correct[$ans]);
      //  $list['Subject'][$subject][$i]['marked_answer']=$marked['ansdata'][$ans];
      // $list['Data'][]=array();
      // $a=$s;
      // if($temp!=$s){
      // $list[$i]['Subject']=$subject;
      // $list[$i]['Subject_id']=$subject_id;
      // $list[$i]['Section']='Section'.$s;
      // }
      // $list['Data'][]['Section'.$s]='Section'.$s;

      // $list[$i]['data']['question_no']=$key;
      // $list[$i]['data']['question_type']=$value;
      // $list[$i]['data']['correct_answer']=static::orcondition($correct[$ans]);
      // $list[$i]['data']['marked_answer']=$marked['ansdata'][$ans];
      $i++;
      $ans++;
      }
      // $new=array_values($list['PHYSICS']['Section1']);
      $sub=Subject::where('subject_id',$id)->pluck('subject_name')[0];
      $new=array();
      if(isset($list[strtoupper($sub)]))
      for ($i=1; $i <=count($list[strtoupper($sub)]) ; $i++) 
      {
          $ob=DB::table($table)
                  ->join('1_exam_admin_create_exam as e','e.sl',$table.'.test_code_sl_id')
                  ->where('test_code_sl_id',$exam_id)
                  ->where('STUD_ID',Auth::id())
                  ->select(strtoupper($sub),'max_marks')->get();
        if(!isset($ob[0]))
          $ob=DB::table($table)
                  ->join('1_exam_admin_create_exam as e','e.sl',$table.'.test_code_sl_id')        
                  ->where('test_code_sl_id',$exam_id)
                  ->where('STUD_ID',$STUD_ID)
                  ->select(strtoupper($sub),'max_marks')->get();

        $sd=array_values($list[strtoupper($sub)]['Section'.$i]);
        $new[$i]['Section']='Section'.$i;
        $new[$i]['question_type']=$sd[key($sd)]['question_type'];
        $new[$i]['Answer']=$sd;
      }
      $new=array_values($new);
      $dr=array_combine($subject_name, explode(',',$ob[0]->max_marks));
      
               $end_time = microtime(true);
               $execution_time = ($end_time - $start_time);
               $end=memory_get_usage(false);
               $used_memory_bytes=$end-$start;
      return 
                     ['Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            ],
                            "Data"=>$new,
                            'subject_id'=>$sbi,
                            "subject_name"=>array_values($subject_name),
                            "mark_obtained"=>$ob[0]->{strtoupper($sub)}."/".$dr[strtoupper($sub)],
                            'used'=>Tparent::get_proper_format($used_memory_bytes),
                           
                            'peak'=>Tparent::get_proper_format(memory_get_peak_usage(true)),
                            'execution_time'=>$execution_time,
                          ];
  }
  // public static function AnswerObtain($data,$ans,$type)
  // {
  //   // dd($type);
  //   $stud=Student::where('ADM_NO',$data->STUD_ID)->get();
  //   $answer1=array();
  //     $ad=0;
  //     $ob=array();
  //    $abcd = array('A'=>1, 'B'=>2,'C'=>3 ,'D'=>4 ,'E'=>5 ,'F'=>6 ,'G'=>7 ,'H'=>8 ,'I'=>9,'U'=>0 );
  //    $msb = array('A'=>1, 'B'=>2,'C'=>3 ,'D'=>4 ,'E'=>5 ,'F'=>6 ,'G'=>7 ,'H'=>8 ,'I'=>9,'U'=>0 );
  //    $nonadv=array('A'=>1,'B'=>2,'C'=>4,'D'=>8,'U'=>0);
  //    $integer=array('U'=>-1,'M'=>-2,'1'=>1,'2'=>2,'3'=>3,'4'=>4,'5'=>5,'6'=>6,'7'=>7,'8'=>8,'9'=>9,'0'=>0);
  //    $pqrst = array('P'=>1, 'Q'=>2, 'R'=>3, 'S'=>4, 'T'=>5, 'U'=>6, 'V'=>7, 'W'=>8, 'X'=>9,'U'=>0); 
  //    $pqrs=array('P'=>1,'Q'=>2,'R'=>3,'S'=>4);
  //    $tf=array('T'=>1,'F'=>2);
  //    $mpw=array('P'=>1,'Q'=>2,'R'=>3,'S'=>4,'W'=>5,'X'=>6,'Y'=>7,'Z'=>8);
  //    $i3="0-999";
  //    $dec="0-999";

  //   if($ans[0]->omr_scanning_type=="advanced")
  //   {
  //   $path='/var/www/html/sri_chaitanya/College/3_view_created_exam/uploads/'.$ans[0]->sl.'/final/'.Auth::user()->CAMPUS_ID.'.iit';
  //   if(isset($data->STUD_ID))
  //      $path='/var/www/html/sri_chaitanya/College/3_view_created_exam/uploads/'.$ans[0]->sl.'/final/'.$stud[0]->CAMPUS_ID.'.iit';
  //   $astring=static::advanced($path,$ans[0]->sl,$data);

  //    $answer=explode(',', $astring['Line']);
  //     $a=1;
  //     $answer1=array_slice($answer, 2);

  //   }
  //   else
  //   {
  //   $path='/var/www/html/sri_chaitanya/College/3_view_created_exam/uploads/'.$ans[0]->sl.'/final/'.Auth::user()->CAMPUS_ID.'.dat';

  //   if(isset($data->STUD_ID))
  //       $path='/var/www/html/sri_chaitanya/College/3_view_created_exam/uploads/'.$ans[0]->sl.'/final/'.$stud[0]->CAMPUS_ID.'.dat';
  //   $astring=static::nonadvanced($path,$ans[0]->sl,$data);
  //    if(count($astring))
  //    $answer1=explode('   ', $astring['Line']);
  //     $a=1;
  //     $ad=1;
  //   }
 
  // for($i=0;$i<=count($answer1)-2;$i++) 
  // {
  //    $temp='';
  //    $arr_num=str_split ($answer1[$i]);
  //   foreach($arr_num as $data)
  //   {
  //     if($ad==1)
  //     {
  //     $temp.=array_search($data,$nonadv);
  //     }
  //     else
  //     {
  //       if(isset($type[$a]))
  //       if($type[$a]=="mb")      
  //     $temp.=array_search($data,$pqrst);
  //       elseif($type[$a]=="i")
  //     $temp.=array_search($data,$integer);  
  //       elseif($type[$a]=="m4")
  //     $temp.=array_search($data,$pqrs);  
  //       elseif($type[$a]=="tf")
  //     $temp.=array_search($data,$tf);  
  //       elseif($type[$a]=="mpw")
  //     $temp.=array_search($data,$mpw);    
  //       else
  //     $temp.=array_search($data,$abcd);
  //     }
  //   }
  //   $answer1[$i]=$temp;
  //   $ob[]=$answer1[$i];
  //   $a++;
  // }
  //   return [
  //         "ansdata"=>$ob,
  //           ];
  // }


// ADVANCED
public static function advanced($filename,$sl,$data){
if(!File::exists($filename))
  return ["Line"=>"file not found"];
else
$lines = file($filename);

$count=0;

$line_count=0;


foreach ($lines as $line_num => $line)
  { 
   $line=trim($line);    


      $current_single_iit_line=$line;
      //correct ias=key file ... stored from ui.. first index leave blank
      
      $current_single_iit_line_array=explode(",",$current_single_iit_line);
    
      $current_usn_with_flag_if_exist=$current_single_iit_line_array[1];
      $current_usn_with_flag_if_exist_array=explode("-",$current_usn_with_flag_if_exist);

      $current_usn=$current_usn_with_flag_if_exist_array[0];



         if(isset($current_usn_with_flag_if_exist_array[1]))
          {
              $current_usn_flag=$current_usn_with_flag_if_exist_array[1];   // FLAG   =>  D,   A  or blank
          }
          else
          {
              $current_usn_flag="blank";  
          }

          if(substr(Auth::id(),2)==$current_usn){
    if($current_usn_flag=="blank"){
    return [
      "Flag"=>$current_usn_flag,
      "USN"=>$current_usn,
      "Line"=>$line,
            ];
          }
    elseif($current_usn_flag=="A"){
      $approve=DB::table('101_mismatch_approval_request')->where('STUD_ID',Auth::id())->where('test_sl',$sl)->where('status',1)->get();
      if(count($approve))
        return [
            "Flag"=>$current_usn_flag,
            "USN"=>$current_usn,
            "Line"=>$line,
                  ];
                  else
                    continue;
    }
    else{
      continue;
    }
        }
           elseif(substr($data->STUD_ID,2)==trim($current_usn)){
         if($current_usn_flag=="blank"){
    return [
      "Flag"=>$current_usn_flag,
      "USN"=>$current_usn,
      "Line"=>$line,
            ];
          }
    elseif($current_usn_flag=="A"){
      $approve=DB::table('101_mismatch_approval_request')->where('STUD_ID',$data->STUD_ID)->where('test_sl',$sl)->where('status',1)->get();
      if(count($approve))
        return [
            "Flag"=>$current_usn_flag,
            "USN"=>$current_usn,
            "Line"=>$line,
                  ];
                  else
                    continue;
    }
    else{
      continue;
    }
      }

      
  }
}


  //NON ADVANCED--------------------------
public static function nonadvanced($filename,$sl,$data){
if(!File::exists($filename))
  return ["Line"=>"file not found"];
else
$lines = file($filename);
$line_count=0;


$count=sizeof($lines); 

$it=$count/4;

//$count=1;

for($in=0;$in<$count;$in=$in+4)
{
   $usnline=trim($lines[$in]);
   $seriesline=trim($lines[$in+1]);
   $qnoline=trim($lines[$in+2]);
   $ansline=trim($lines[$in+3]);
 //DELETE
   $usnlinearray=explode("=",$usnline);   //.  No.=9277048-D
   
   //echo json_encode($usnlinearray);
   $current_usn_with_if_flag=$usnlinearray[1]; // 9277048-D
   $usn_with_flag_array=explode("-",$current_usn_with_if_flag);


   $current_usn=$usn_with_flag_array[0];



         if(isset($usn_with_flag_array[1]))
          {
              $current_usn_flag=$usn_with_flag_array[1];   // FLAG   =>  D,   A  or blank
          }
          else
          {
              $current_usn_flag="blank";  
          }



   $current_usn_flag=trim($current_usn_flag);
   //echo "curf=".$current_usn_flag;echo "<br>";//exit;
   $only_usn=$usn_with_flag_array[0];
   $current_usn=$only_usn;
// return 


  // print_r($current_usn.'<br>');

   // if($current_usn=='8170611')
   // dd($current_usn_flag);



  if(substr(Auth::id(),2)==trim($current_usn)){
    if($current_usn_flag=="blank"){
    return [
    "Flag"=>$current_usn_flag,
    "USN"=>$current_usn,
    "Line"=>$ansline,
          ];
        }
     elseif($current_usn_flag=="A"){
  $approve=DB::table('101_mismatch_approval_request')->where('STUD_ID',Auth::id())->where('test_sl',$sl)->where('status',1)->get();
  if(count($approve)!=0){
    return [
        "Flag"=>$current_usn_flag,
        "USN"=>$current_usn,
        "Line"=>$ansline,
              ];
            }
            else{
              continue;
            }
       }
    else{
      continue;
    }
      }
      elseif(substr($data->STUD_ID,2)==trim($current_usn)){

         if($current_usn_flag=="blank"){
    return [
    "Flag"=>$current_usn_flag,
    "USN"=>$current_usn,
    "Line"=>$ansline,
          ];
        }
     elseif($current_usn_flag=="A"){
  $approve=DB::table('101_mismatch_approval_request')->where('STUD_ID',$data->STUD_ID)->where('test_sl',$sl)->where('status',1)->get();
  if(count($approve)!=0){
    return [
        "Flag"=>$current_usn_flag,
        "USN"=>$current_usn,
        "Line"=>$ansline,
              ];
            }
            else{
              continue;
            }
       }
    else{
      continue;
    } 
      }

   }

   return array();
  }
  public static function orcondition($data){
    $new="";
    $ar=array();
    if(substr($data, 0, 2)=="OR")
    {
      $ar=explode('-',$data);
      unset($ar[0]);
      foreach (array_values($ar) as $key => $value) 
      {
        if($new=="")
        $new.=$value;
      else
        $new.=' or '.$value;

      }
      return $new;
    }
    return $data;

  }

}
