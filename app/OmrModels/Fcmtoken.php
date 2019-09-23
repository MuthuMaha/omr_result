<?php

namespace App\OmrModels;
use DB;
use Illuminate\Database\Eloquent\Model;
use App\Http\Resources\NotifyCollection;
use App\BaseModels\Student;
use App\BaseModels\Campus;
class Fcmtoken extends Model
{
    protected $table='fcm_tokens';
    protected $fillable=['token','USERID','user_type'];

    public static function notifications($data)
    {
      $arr1=array();
      $arr=Sendnotifications::where(
    'USERID',$data->USERID)->get();
      // foreach ($arr as $key => $value) {
        if(count($arr)!=0)
      $arr1=new NotifyCollection(
        Notifymessage::whereIn('id',explode(',',$arr[0]->notification_ids))->orderby('created_at','asc')->get());
      // }
      return  ['Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            ],
                 "Data"=>$arr1
                ];
    }
    public static function sendmessage($data)
    {
      $abc=array();
      $api_key=Exam::where('sl',$data->exam_id)->pluck('api_key')[0];
      if($data->api_key==$api_key){
      if($data->notify_type==0)
      $type='Exam Created';
      if($data->notify_type==1)
      $type='Rank Generated';

        $a=array();
         $fcm="";
         $notify="";
         $exam=Exam::
         join('0_test_modes as tm','1_exam_admin_create_exam.mode','=','tm.test_mode_id')
         ->join('0_test_types as tt','1_exam_admin_create_exam.test_type','=','tt.test_type_id')
         ->where('sl',$data->exam_id)->get();
         // dd($exam);
         $title=$exam[0]->test_code;
         $gcsp=Exam::
         join('1_exam_gcsp_id as a','a.test_sl','=','1_exam_admin_create_exam.sl')
         ->where('a.test_sl',$data->exam_id)
         ->get();
         $campus=Campus::where('STATE_ID',$exam[0]->state_id)->pluck('CAMPUS_ID');
// return $campus;
         $student[]=DB::table('t_employee as e')
           ->join('ip_exam_section as i','i.EMPLOYEE_ID','=','e.PAYROLL_ID')
           ->join('t_college_section as tc','tc.SECTION_ID','=','i.SECTION_ID')
           ->join('t_course_group as g','g.GROUP_ID','=','i.GROUP_ID')
           ->join('t_study_class as c','c.CLASS_ID','=','i.CLASS_ID')
           ->join('t_stream as st','st.STREAM_ID','=','i.STREAM_ID')
           ->join('t_program_name as p','p.PROGRAM_ID','=','i.PROGRAM_ID')
           ->join('0_subjects as ts','ts.SUBJECT_ID','i.SUBJECT_ID')
           ->whereIn('e.CAMPUS_ID',$campus)
           ->where('e.PAYROLL_ID','<>','')
           ->select('e.PAYROLL_ID as USERID','i.GROUP_ID','i.CLASS_ID','i.STREAM_ID','i.PROGRAM_ID','i.SUBJECT_ID','g.GROUP_NAME','c.CLASS_NAME','st.STREAM_NAME','p.PROGRAM_NAME','ts.SUBJECT_NAME')->get();
           if($data->notify_type==0)
         foreach($gcsp as $key=>$value){
         $student[]=Student::
           join('t_course_group as g','g.GROUP_ID','=','scaitsqb.t_student_bio.GROUP_ID')
           ->join('t_study_class as c','c.CLASS_ID','=','scaitsqb.t_student_bio.CLASS_ID')
           ->join('t_stream as st','st.STREAM_ID','=','scaitsqb.t_student_bio.STREAM_ID')
           ->join('t_program_name as p','p.PROGRAM_ID','=','scaitsqb.t_student_bio.PROGRAM_ID')
          ->where('g.group_id',$value->GROUP_ID)
         ->where('c.class_id',$value->CLASS_ID)
         ->where('st.stream_id',$value->STREAM_ID)
         ->where('p.program_id',$value->PROGRAM_ID)
         ->whereIn('scaitsqb.t_student_bio.CAMPUS_ID',$campus)
         ->distinct('ADM_NO')
         ->select('ADM_NO as USERID','g.GROUP_ID','c.CLASS_ID','st.STREAM_ID','p.PROGRAM_ID','scaitsqb.t_student_bio.CAMPUS_ID as SUBJECT_ID','NAME as SUBJECT_NAME','g.GROUP_NAME','c.CLASS_NAME','st.STREAM_NAME','p.PROGRAM_NAME')->get()
         ;         
         }
         else
          $student[]=DB::table('101_mpc_marks')
        ->join('scaitsqb.t_student_bio as st','st.ADM_NO','=','101_mpc_marks.STUD_ID')        
           ->join('t_course_group as g','g.GROUP_ID','=','st.GROUP_ID')
           ->join('t_study_class as c','c.CLASS_ID','=','st.CLASS_ID')
           ->join('t_stream as s','s.STREAM_ID','=','st.STREAM_ID')
           ->join('t_program_name as p','p.PROGRAM_ID','=','st.PROGRAM_ID')
        ->where('test_code_sl_id',$data->exam_id)
         ->select('ADM_NO as USERID','g.GROUP_ID','c.CLASS_ID','st.STREAM_ID','p.PROGRAM_ID','st.CAMPUS_ID as SUBJECT_ID','st.NAME as SUBJECT_NAME','g.GROUP_NAME','c.CLASS_NAME','st.STREAM_NAME','p.PROGRAM_NAME')->get();
         ;
          $fcmUrl = 'https://fcm.googleapis.com/fcm/send';            
          $url='exam_list';
          $d=date('Y-m',strtotime($exam[0]->start_date));
          foreach ($student as $key => $value) {
          foreach ($value as $key1 => $value1) {
           
        $body='{"mode_id":'.$exam[0]->mode.',"test_type_id":'.$exam[0]->test_type.',"exam_id":'.$data->exam_id.',"USERID":"'.$value1->USERID.'","group_id":"'.$value1->GROUP_ID.'","class_id":"'.$value1->CLASS_ID.'","stream_id":"'.$value1->STREAM_ID.'","program_id":"'.$value1->PROGRAM_ID.'","group_name":"'.$value1->GROUP_NAME.'","class_name":"'.$value1->CLASS_NAME.'","stream_name":"'.$value1->STREAM_NAME.'","program_name":"'.$value1->PROGRAM_NAME.'","subject_name":"'.$value1->SUBJECT_NAME.'","subject_id":"'.$value1->SUBJECT_ID.'","date":"'.$d.'","test_mode":"'.$exam[0]->test_mode_name.'","model_year":"'.$exam[0]->model_year.'_'.$exam[0]->paper.'"}';

          $notification = [
                 'title' => $title,
                 'parameter'=>$body,
                 'url'=>$url,
                 "notify_type"=>$type,
             ];
             $notify=Notifymessage::updateOrcreate(['title'=>$title,'notify_type'=>$type,'parameter'=>$body],$notification);


              $se1=Sendnotifications::where('USERID',$value1->USERID)->get();

             if(isset($se1[0]) && isset($notify)){
              $se=Sendnotifications::where('USERID',$value1->USERID)->get();
              if(isset($se[0]) )
                 $send=Sendnotifications::where([
                    "USERID"=>$se[0]->USERID,])->update([
                    "notification_ids"=>$se[0]->notification_ids.','.$notify->id,
               ]);

             }
             elseif(isset($notify)){
                                  
             $send=Sendnotifications::create([
                  "notification_ids"=>$notify->id,        
                  "USERID"=>$value1->USERID,
                                              ]);
               }
               else{}
             

               $token=Fcmtoken::where('USERID',$value1->USERID)->orderby('created_at','DESC')->distinct('token')->pluck('token');
             $abc[]=$token;
               foreach ($token as $key3 => $value3) {
                $cx=json_decode($notify->parameter);
                $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
                         $notification = [
                       'title' => $notify->title,
                       'url'=>$notify->url,
                       "notify_type"=>$notify->notify_type,
                   ];
                   $notification['mode_id']=$cx->mode_id;
                   $notification['test_type_id']=$exam[0]->test_type_name;
                   $notification['exam_id']=$cx->exam_id;
                   $notification['USERID']=$cx->USERID;
                   $notification['date']=$cx->date;
                   $notification['test_mode']=$cx->test_mode;
                   $notification['model_year']=$cx->model_year;
                   $notification['start_date']=$exam[0]->start_date;
                 $fcmNotification = [
                     'to'   =>$value3,
                     'data' => $notification
                 ];
                  $headers = [
                 'Authorization: key=AAAAKOCFNDk:APA91bGymao4PPgiubS42HVwSF0Ifbvuz546g7SpN03dky2I2QEf0dm3_qfOMjeGDzy91zU_YNEFme7UwJsKQ8su5ShokzmNxxkQn_IXM6J92qtVcusy7Hp3HnhADYGs5qs3U9qsFJTD',
                 'Content-Type: application/json'
             ];
                  $ch = curl_init();
                   curl_setopt($ch, CURLOPT_URL,$fcmUrl);
                   curl_setopt($ch, CURLOPT_POST, true);
                   curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                   curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
                   curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
                   $result = curl_exec($ch);
                   curl_close($ch);

                $a[]= json_decode($result);
               }
          }
         }
        return $a;
      }
      else{
        return "<!DOCTYPE html><html><head><title>Auth Failed</title></head><body style='margin-top:10%;font-size:8rem;'><center><b>Authentication Failed<br>Check the API_KEY once</b></center></body></html>";
      }
    }
}
?>
