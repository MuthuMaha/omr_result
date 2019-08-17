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
      // return $data->api_key;
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
           // ->join('t_course_track as tt','tt.COURSE_TRACK_ID','=','tc.COURSE_TRACK_ID')
           ->whereIn('e.CAMPUS_ID',$campus)
           // ->where('tt.GROUP_ID',$value->GROUP_ID)
           // ->where('tt.CLASS_ID',$value->CLASS_ID)
           // ->where('tt.STREAM_ID',$value->STREAM_ID)
           // ->where('tc.PROGRAM_ID',$value->PROGRAM_ID)
           ->where('e.PAYROLL_ID','<>','')
           ->distinct('e.PAYROLL_ID')
           ->pluck('e.PAYROLL_ID');
           if($data->notify_type==0)
         foreach($gcsp as $key=>$value){
         $student[]=Student::
          where('group_id',$value->GROUP_ID)
         ->where('class_id',$value->CLASS_ID)
         ->where('stream_id',$value->STREAM_ID)
         ->where('program_id',$value->PROGRAM_ID)
         ->whereIn('CAMPUS_ID',$campus)
         ->distinct('ADM_NO')
         ->pluck('ADM_NO')
         ;         
         }
         else
          $student[]=DB::table('101_mpc_marks')->where('test_code_sl_id',$data->exam_id)->pluck('STUD_ID as ADM_NO');

         // return $student;
          $fcmUrl = 'https://fcm.googleapis.com/fcm/send';            
          $url='exam_list';
          $d=date('Y-m',strtotime($exam[0]->start_date));

         foreach ($student as $key => $value) {
          foreach ($value as $key1 => $value1) {
        $body='{"mode_id":'.$exam[0]->mode.',"test_type_id":'.$exam[0]->test_type.',"exam_id":'.$data->exam_id.',"USERID":"'.$value1.'","date":"'.$d.'","test_mode":"'.$exam[0]->test_mode_name.'","model_year":"'.$exam[0]->model_year.'_'.$exam[0]->paper.'"}';
          $notification = [
                 'title' => $title,
                 'parameter'=>$body,
                 'url'=>$url,
                 "notify_type"=>$type,
             ];
             $notify=Notifymessage::updateOrcreate(['title'=>$title,'notify_type'=>$type],$notification);


              $se1=Sendnotifications::where('USERID',$value1)->get();

             if(isset($se1[0]) && isset($notify)){
              $se=Sendnotifications::where('USERID',$value1)->get();
              // $u=implode(',', $se[0]->notification_ids);
              // $z=array_search($notify->id, $se[0]->notification_ids);
              if(isset($se[0]) )
                 $send=Sendnotifications::where([
                    "USERID"=>$se[0]->USERID,])->update([
                    "notification_ids"=>$se[0]->notification_ids.','.$notify->id,
               ]);

             }
             elseif(isset($notify)){
                                  
             $send=Sendnotifications::create([
                  "notification_ids"=>$notify->id,        
                  "USERID"=>$value1,
                                              ]);
               }
               else{}
             

               $token=Fcmtoken::where('USERID',$value1)->orderby('created_at','DESC')->distinct('token')->pluck('token');
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
