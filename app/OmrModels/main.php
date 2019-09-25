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
class main 
{
  
 
public static function AnswerObtain($data,$ans,$type)
  {
    // dd($type);
    $stud=Student::where('ADM_NO',$data->STUD_ID)->get();
    $answer1=array();
      $ad=0;
      $ob=array();
     $abcd = array('A'=>1, 'B'=>2,'C'=>3 ,'D'=>4 ,'E'=>5 ,'F'=>6 ,'G'=>7 ,'H'=>8 ,'I'=>9,'U'=>0 );
     
     $msb = array('A'=>1, 'B'=>2,'C'=>3 ,'D'=>4 ,'E'=>5 ,'F'=>6 ,'G'=>7 ,'H'=>8 ,'I'=>9,'U'=>0 );

     $nonadv=array('A'=>1,'B'=>2,'C'=>4,'D'=>8,'U'=>0);

     $integer=array('U'=>-1,'M'=>-2,'1'=>1,'2'=>2,'3'=>3,'4'=>4,'5'=>5,'6'=>6,'7'=>7,'8'=>8,'9'=>9,'0'=>0);

     $pqrst = array('P'=>1, 'Q'=>2, 'R'=>3, 'S'=>4, 'T'=>5,'U'=>0); 

     $pqrs=array('P'=>1,'Q'=>2,'R'=>3,'S'=>4,'U'=>0);

     $tf=array('T'=>1,'F'=>2,'U'=>0,'TF'=>12);

     $mpw=array('P'=>1,'Q'=>2,'R'=>3,'S'=>4,'W'=>5,'X'=>6,'Y'=>7,'Z'=>8,'U'=>0);

    if($ans[0]->omr_scanning_type=="advanced")
    {
    $path='/var/www/html/sri_chaitanya/College/3_view_created_exam/uploads/'.$ans[0]->sl.'/final/'.Auth::user()->CAMPUS_ID.'.iit';
    if(isset($data->STUD_ID))
       $path='/var/www/html/sri_chaitanya/College/3_view_created_exam/uploads/'.$ans[0]->sl.'/final/'.$stud[0]->CAMPUS_ID.'.iit';
    $astring=Exam::advanced($path,$ans[0]->sl,$data);

     $answer=explode(',', $astring['Line']);
      $a=1;
      $answer1=array_slice($answer, 2);
    }
    else
    {
    $path='/var/www/html/sri_chaitanya/College/3_view_created_exam/uploads/'.$ans[0]->sl.'/final/'.Auth::user()->CAMPUS_ID.'.dat';

    if(isset($data->STUD_ID))
        $path='/var/www/html/sri_chaitanya/College/3_view_created_exam/uploads/'.$ans[0]->sl.'/final/'.$stud[0]->CAMPUS_ID.'.dat';
    $astring=Exam::nonadvanced($path,$ans[0]->sl,$data);
     if(count($astring))
     $answer1=explode('   ', $astring['Line']);
   if(count($answer1)==1)
    $answer1=array();
      $a=1;
      $ad=1;
    }
 
  for($i=0;$i<=count($answer1)-1;$i++) 
  {
     $temp='';
     $arr_num=str_split ($answer1[$i]);
    
    foreach($arr_num as $data)
    {
      if($ad==1)
      {
      $temp.=array_search($data,$nonadv);
      }
      else
      {
        if(isset($type[$a]))
        if($type[$a]=="mb")      
      $temp.=array_search($data,$pqrst);
        elseif($type[$a]=="i")
      $temp.=array_search($data,$integer);  
        elseif($type[$a]=="m4")
      $temp.=array_search($data,$pqrs);  
        elseif($type[$a]=="tf")
      $temp.=array_search($data,$tf);  
        elseif($type[$a]=="mpw")
      $temp.=array_search($data,$mpw);    
        elseif($type[$a]=="i3")
      $temp.=array_search($data,$integer);   
       elseif($type[$a]=="dec")
      $temp.=array_search($data,$integer);    
        else
      $temp.=array_search($data,$abcd);
      }

    }
    if(isset($type[$a]) && $type[$a]=="dec")
    $answer1[$i]=($temp/100);
   //    }
   else
    $answer1[$i]=$temp;
   //    }
      
   // }
 
    
    $ob[]=$answer1[$i];
    $a++;
  }
    return [
          "ansdata"=>$ob,
            ];
  }

}

?>