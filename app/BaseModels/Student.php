<?php

namespace App\BaseModels;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use App\Token;
use App\OmrModels\role;
use App\OmrModels\Exam;
use App\Ipmpc;
use Auth;

class Student extends Authenticatable
{
    //
    
    use Notifiable;
    // protected $connection='mysql2';
    protected $table = 'scaitsqb.t_student_bio';
    protected $primaryKey = 'ADM_NO';
    private static $test_types=[];
      public function getBodyAttributes($value)
    {
        return ucfirst(strtolower($value));
    }

     public function roles()
    {
        return $this->belongsToMany('App\OmrModels\role');
    }


     public function program()
    {
        return $this->hasOne('App\BaseModels\Program','PROGRAM_ID', 'PROGRAM_ID');
    }
     public function parent()
    {
        return $this->hasOne('App\BaseModels\Parents','ADM_NO', 'ADM_NO');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */

     public function tokens() {
        return $this->belongsTo(Token::class, 'user_id', 'ADM_NO');
    }
    public function stream()
    {
        return $this->hasOne('App\BaseModels\Stream','STREAM_ID','STREAM_ID');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function class_year()
    {
        return $this->hasOne('App\BaseModels\StudyClass','CLASS_ID','CLASS_ID');
    }

    public function campus()
    {
        return $this->hasOne('App\BaseModels\Campus','CAMPUS_ID','CAMPUS_ID');
    }

    public function section()
    {
        return $this->hasOne('App\BaseModels\Section','SECTION_ID','SECTION_ID');
    }

    public static function profile($stud_id){
        return static::where('ADM_NO','=',$stud_id)->with('program','stream','class_year','campus','section','parent')->get();

    }


    public static function written_tests($data){

        $test_types=DB::table('0_test_types')->where('test_type_id',$data->test_type_id)->get();
     //    foreach($test_types as $value){
            
          $query[$test_types[0]->test_type_name] = DB::select("select ipd.Exam_name,ipd.Exam_id,
                         IF(ipd.path!='', 'True', 'False') as Is_Result_Uploaded from IP_Exam_Details ipd left join IP_Exam_Conducted_For ecf on ipd.exam_id=ecf.Exam_id inner join (select t.CAMPUS_ID,ct.GROUP_ID,pn.PROGRAM_ID,t.class_id,ts.STREAM_ID from t_student t left join t_course_track ct on t.COURSE_TRACK_ID=ct.COURSE_TRACK_ID left join t_study_class sc on sc.class_id=t.class_id left join t_program_name pn on t.PROGRAM_ID=pn.PROGRAM_ID left join t_stream ts on ts.STREAM_ID=t.stream_id WHERE t.adm_no='".Auth::id()."') ds on ecf.classyear_id=ds.class_id and ecf.stream_id=ds.stream_id and ecf.program_id=ds.program_id and ecf.exam_id=ipd.exam_id and ds.group_id = ecf.group_id and ipd.Test_type_id='".$data->test_type_id."'"
                    );   

        // }
          $object = new \stdClass(); 
          $object->EXAM_ID = $query[$test_types[0]->test_type_name][0]->Exam_id;
          $object->test_type_id = $data->test_type_id;
          $query[$test_types[0]->test_type_name][0]->Percentages=Ipmpc::markDetails($object)["OverAll_Averages"];

           
          // echo $sum;


          $query[$test_types[0]->test_type_name][0]->total=Ipmpc::markDetails($object)["Add"];
          // $query[$test_types[0]->test_type_name][0]->Result=Ipmpc::markDetails($object)["Result"];
 
       return [
                        'Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            ],
                            'Details'=>$query,
                            // 'MarkDetails'=>Ipmpc::markDetails($object)["Result"],
                            // 'OverAll_Averages'=>Ipmpc::markDetails($object)["OverAll_Averages"],
                    ];

    }


    public static function written_tests_date($data){
        
            $dateValue = strtotime($data->test_date);

            $yr = date("m-Y", $dateValue); 
                    $test_types=DB::table('0_test_types')->where('test_type_id',$data->test_type_id)->get();
                      $query[$test_types[0]->test_type_name] = DB::select("select ipd.Exam_name,ipd.exam_id,
                         IF(ipd.path!='', 'True', 'False') as Is_Result_Uploaded from IP_Exam_Details ipd left join IP_Exam_Conducted_For ecf on ipd.exam_id=ecf.Exam_id inner join (select t.CAMPUS_ID,ct.GROUP_ID,pn.PROGRAM_ID,t.class_id,ts.STREAM_ID from t_student t left join t_course_track ct on t.COURSE_TRACK_ID=ct.COURSE_TRACK_ID left join t_study_class sc on sc.class_id=t.class_id left join t_program_name pn on t.PROGRAM_ID=pn.PROGRAM_ID left join t_stream ts on ts.STREAM_ID=t.stream_id WHERE t.adm_no='".Auth::id()."') ds on ecf.classyear_id=ds.class_id and ecf.stream_id=ds.stream_id and ecf.program_id=ds.program_id and ecf.exam_id=ipd.exam_id and ds.group_id = ecf.group_id and ipd.Test_type_id='".$data->test_type_id."' and ipd.Date_exam LIKE '%".$yr."'");   
 
     $object = new \stdClass(); 
          $object->EXAM_ID = $query[$test_types[0]->test_type_name][0]->exam_id;
          $object->test_type_id = $data->test_type_id;
   
          $query[$test_types[0]->test_type_name][0]->Percentages=Ipmpc::markDetails($object)["OverAll_Averages"];

           
          // echo $sum;


          $query[$test_types[0]->test_type_name][0]->total=Ipmpc::markDetails($object)["Add"];
          // $query[$test_types[0]->test_type_name][0]->Result=Ipmpc::markDetails($object)["Result"];
 
       return [
                        'Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            ],
                            'Details'=>$query,
                            // 'MarkDetails'=>Ipmpc::markDetails($object)["Result"],
                            // 'OverAll_Averages'=>Ipmpc::markDetails($object)["OverAll_Averages"],
                    ];


    }
    public static function studlist($request){
      $res=Student::from('scaitsqb.t_student_bio as a')
        // ->join('1_exam_gcsp_id as b','b.STUD_ID','=','a.ADM_NO')
        ->join('t_college_section as b','b.SECTION_ID','=','a.SECTION_ID')
        ->join('ip_exam_section as is','is.SECTION_ID','=','b.SECTION_ID')
        ->where('a.GROUP_ID',$request->group_id)
        ->where('a.CLASS_ID',$request->class_id)
        ->where('a.STREAM_ID',$request->stream_id)
        ->where('a.PROGRAM_ID',$request->program_id);
            if(isset($request->CAMPUS_ID)){
            $res->where('a.CAMPUS_ID',$request->CAMPUS_ID);
            $res->where('b.section_name','<>','NOT_ALLOTTED');
            $res->select('a.SECTION_ID','b.section_name');
            $res->distinct('a.SECTION_ID');

                    if(isset($request->SECTION_ID)){
                    $res->where('a.SECTION_ID',$request->SECTION_ID);
                    $res->select('a.ADM_NO','a.CAMPUS_NAME','a.NAME','a.GROUP_ID','a.CLASS_ID','a.STREAM_ID','a.PROGRAM_ID');
                    }
                }
            else{
            $res->select('a.CAMPUS_ID','a.CAMPUS_NAME');
            $res->distinct('a.CAMPUS_ID');
            }
        $res=$res->get();
        if(isset($request->SECTION_ID))
          $res=static::addexam($res);
        return $res;
    }
    public static function addexam($list)
    {
      $new=array();
      $data=array();
      // return $list;
      if(isset($list[0])){
      $tt=['101_mpc_marks','102_bipc_marks'];
      foreach($list as $o) {
            $stud_id[] = $o->ADM_NO;
        }
        $a['Exam_ID']=DB::table('1_exam_gcsp_id as gcsp')
                                  ->where('GROUP_ID',$list[0]->GROUP_ID)
                                  ->where('CLASS_ID',$list[0]->CLASS_ID)
                                  ->where('STREAM_ID',$list[0]->STREAM_ID)
                                  ->where('PROGRAM_ID',$list[0]->PROGRAM_ID)
                                  ->pluck('test_sl');
     
        $stude=Exam::from('1_exam_admin_create_exam as e')
        ->join('0_test_modes as m','e.mode','=','m.test_mode_id')
        ->whereIn('sl',$a['Exam_ID'])
        ->select('max_marks','marks_upload_final_table_name as table','sl')
        ->get();
          
        foreach($stude as $o) {
            $tdeno[] =array_sum(explode(',',$o->max_marks));
         }

         // $tt=array_unique($tt);
        $cdeno=count($stude);
        $tde=array_sum($tdeno);
        // return ;
        foreach ($tt as $key1 => $value1) {
          $b=DB::table($value1)
          ->join('scaitsqb.t_student_bio as st','st.ADM_NO','=',$value1.'.STUD_ID')
          ->whereIn('STUD_ID',$stud_id)
          ->whereIn('test_code_sl_id',$a['Exam_ID'])
          ->select('TOTAL','STUD_ID','test_code_sl_id','NAME','CAMPUS_NAME')->get();
          if(isset($b[0]))
            $data[]=$b;
        }
        if(isset($data[0]))
        foreach ($data[0] as $key => $value) 
        {
          foreach ($stude as $key1 => $value1) {
              if($value->test_code_sl_id==$value1->sl)
              if(isset($new[$value->STUD_ID]['mark'])){
                $new[$value->STUD_ID]['mark']+=$value->TOTAL;
                $new[$value->STUD_ID]['count']++;

              }
              else{
                $new[$value->STUD_ID]['mark']=$value->TOTAL;
                $new[$value->STUD_ID]['count']=1;

              }
              if(isset($new[$value->STUD_ID]['mark']) && isset($new[$value->STUD_ID]['count'])){
              $new[$value->STUD_ID]['tmark']=$new[$value->STUD_ID]['mark'].'/'.$tde;
              $new[$value->STUD_ID]['tcount']=$new[$value->STUD_ID]['count'].'/'.$cdeno;
              }
              $new[$value->STUD_ID]['STUD_ID']=$value->STUD_ID;
              $new[$value->STUD_ID]['STUD_NAME']=$value->NAME;
              $new[$value->STUD_ID]['CAMPUS_NAME']=$value->CAMPUS_NAME;
          }
        }
       $new=array_values($new);
         
      
      }
      return $new;
    }



}
