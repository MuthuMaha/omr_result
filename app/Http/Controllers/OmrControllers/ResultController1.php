<?php
namespace App\Http\Controllers\OmrControllers;
use DB;
use Auth;
use Validator;
use Illuminate\Http\Request;
use App\BaseModels\Group as groups;
use App\BaseModels\StudyClass as class_year;
use App\Http\Resources\Group as GroupResource;
use App\BaseModels\CourseTrack as course_track;
use App\BaseModels\Stream;
use App\BaseModels\Program;
use App\BaseModels\Student;
use App\BaseModels\Section;
use App\OmrModels\Subject;
use App\Temployee;
use App\Http\Controllers\Controller;
use App\OmrModels\section as Examsection;
use App\Http\Resources\GroupCollection;
use App\Http\Resources\StudyClassCollection;
use App\Http\Resources\StreamCollection;
use App\Http\Resources\ProgramCollection;


class ResultController1 extends Controller
{

    public function groups(Request $request)
    {
        $filter=Examsection::from('IP_Exam_Section as s')
                    ->join('t_college_section as tc','s.SECTION_ID','=','tc.section_id')
                    ->join('t_course_track as t','t.COURSE_TRACK_ID','=','tc.COURSE_TRACK_ID')
                    ->where('s.EMPLOYEE_ID',Auth::user()->payroll_id)
                    ->where('t.STREAM_ID','<>','')
                    ->where('tc.PROGRAM_ID','<>','')
                    ->select('t.GROUP_ID','t.CLASS_ID','t.STREAM_ID','tc.PROGRAM_ID','s.subject_id');
                    $filter=$filter->get();
                    foreach ($filter as $key => $value) {
                       $group1[$key]['subject']['subject_id']=$value->subject_id;
                        $group1[$key]['subject']['subject_name']=Subject::where('subject_id',$value->subject_id)->select('subject_name')->get()[0]->subject_name;
                       $group1[$key]['group']['GROUP_ID']=$value->GROUP_ID;
                        $group1[$key]['group']['GROUP_NAME']=groups::where('GROUP_ID',$value->GROUP_ID)->select('GROUP_NAME')->get()[0]->GROUP_NAME;
                       $group1[$key]['class']['CLASS_ID']=$value->CLASS_ID;
                        $group1[$key]['class']['CLASS_NAME']=class_year::where('CLASS_ID',$value->CLASS_ID)->select('CLASS_NAME')->get()[0]->CLASS_NAME;
                       $group1[$key]['stream']['STREAM_ID']=$value->STREAM_ID;
                        $group1[$key]['stream']['STREAM_NAME']=Stream::where('STREAM_ID',$value->STREAM_ID)->select('STREAM_NAME')->get()[0]->STREAM_NAME;
                       $group1[$key]['program']['PROGRAM_ID']=$value->PROGRAM_ID;
                        $group1[$key]['program']['PROGRAM_NAME']=Program::where('PROGRAM_ID',$value->PROGRAM_ID)->select('PROGRAM_NAME')->get()[0]->PROGRAM_NAME;
                    }

$group=DB::table('IP_Exam_Section as s')->join('t_college_section as tc','s.SECTION_ID','=','tc.section_id')->join('t_course_track as t','t.COURSE_TRACK_ID','=','tc.COURSE_TRACK_ID')->where('s.EMPLOYEE_ID',Auth::user()->payroll_id)->pluck('t.GROUP_ID');
$query=groups::distinct('GROUP_ID')->orderBy('GROUP_ID');
        if(count($group)!=0)
        $query->whereIn('GROUP_ID',$group);
    $query->orwhere('GROUP_ID','5');
        $query=$query->get();
        $data=new GroupCollection($query);
        // $data1=array_merge(['GROUP_ID'=>5,'GROUP_NAME'=>"MPC"],['GROUP_ID'=>5,'GROUP_NAME'=>"MPC"]);
        return [
                  
                     'Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            ],
                            "data"=>$data];

    }
    public function class_year_wrt_group(Request $request, $id)
    {
         $class=DB::table('IP_Exam_Section as s')->join('t_college_section as tc','s.SECTION_ID','=','tc.section_id')->join('t_course_track as t','t.COURSE_TRACK_ID','=','tc.COURSE_TRACK_ID')->where('s.EMPLOYEE_ID',Auth::user()->payroll_id)->pluck('t.CLASS_ID');
        $query=class_year::whereIn('CLASS_ID',course_track::where('GROUP_ID',$id)->pluck('CLASS_ID'));
        if(count($class)!=0)
            $query->whereIn('CLASS_ID',$class);
        $query=$query->get();
        $data=new StudyClassCollection($query);  
         return [
                  
                     'Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            ],
                            "data"=>$data];
    
    
    }
    public function stream_wrt_group_class_year(Request $request)
    { 
      
         $stream=DB::table('IP_Exam_Section as s')->join('t_college_section as tc','s.SECTION_ID','=','tc.section_id')->join('t_course_track as t','t.COURSE_TRACK_ID','=','tc.COURSE_TRACK_ID')->where('s.EMPLOYEE_ID',Auth::user()->payroll_id)->pluck('t.STREAM_ID');  
        //Get group and class year to filter required streams
        $group_id= $request->group_id;
        $class_id= $request->class_id;
        $query=Stream::whereIn('STREAM_ID',course_track::distinct('STREAM_ID')
                            ->where('STREAM', '<>','NULL')
                            ->where('GROUP_ID',$group_id)
                            ->where('CLASS_ID',$class_id)->get());
        // if(count($stream)!=0)
        //     $query->whereIn('STREAM_ID',$stream);
        $query=$query->get();
        $data=new StreamCollection($query);
         return [
                  
                     'Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            ],
                            "data"=>$data];

         
    }

    public function programs_wrt_stream_class_year(Request $request)
    {
         $program=DB::table('IP_Exam_Section as s')->join('t_college_section as tc','s.SECTION_ID','=','tc.section_id')->join('t_course_track as t','t.COURSE_TRACK_ID','=','tc.COURSE_TRACK_ID')->where('s.EMPLOYEE_ID',Auth::user()->payroll_id)->pluck('tc.PROGRAM_ID');  

        //Get stream and class id for required programs
        $stream_id= $request->stream_id;
        $class_id= $request->class_id;
        // $query=Program::
        // where('STREAM_ID', '=',$stream_id);
        //     ->where('CLASS_ID',$class_id);
        // if(count($program))
        $query=Program::whereIn('PROGRAM_ID',$program)->orwhere('PROGRAM_ID','1');
        $query=$query->get();
        $data=new ProgramCollection($query); 
        return [
                  
                     'Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            ],
                            "data"=>$data];



    }
    public function search(Request $request){
        if($request->user_type=="employee" || $request->user_type=="director" )
        $res=Temployee::where('PAYROLL_ID','like','%'.$request->USERID.'%')->select('PAYROLL_ID','NAME')->get();
        else
        $res=Student::where('ADM_NO','like','%'.$request->USERID.'%')->select('ADM_NO','NAME')->get();
        return [
                     'Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            ],
            "Data"=>$res];

    }
}
