<?php

namespace App\OmrModels;
use DB;
use Auth;
use App\BaseModels\Student;
use Illuminate\Database\Eloquent\Model;
include_once($_SERVER['DOCUMENT_ROOT'].'/sri_chaitanya/Exam_Admin/3_view_created_exam/z_ias_format.php');

class Modesyear extends Model
{
  protected $table='0_test_modes_years';
  public $timestamps=false;
  
    public static function exam_info($data,$ch){
    	
    	$exam=Exam::where('sl',$data->exam_id)->select('key_answer_file_long_string as CorrectAnswer','model_year','paper','omr_scanning_type','to_from_range','subject_string_final','sl','test_code','mode','mark_file_long_string','max_marks')->get();
    	// return $exam;
    	$table=Mode::where('test_mode_id',$exam[0]->mode)->pluck('marks_upload_final_table_name');
    	if($ch==1 || isset($data->STUD_ID))
    	$result=DB::table($table[0])->where('STUD_ID',$data->STUD_ID)->where('test_code_sl_id',$data->exam_id)->get();
    	else
    	$result=DB::table($table[0])->where('STUD_ID',Auth::id())->where('test_code_sl_id',$data->exam_id)->get();
    	if(count($result)==0){
    		return [
                        'Login' => [
                            'response_message'=>"Student Record Not Found",
                            'response_code'=>"0"
                           ],
                    ];
    	}
    	if($exam[0]->omr_scanning_type=="advanced"){
	$response_array=ias_model_year_paper($exam[0]->model_year,$exam[0]->paper);
		}
		else{
			foreach (explode(',',$exam[0]->subject_string_final) as $key => $value) {
	$sub[]=DB::table('0_subjects')->where('subject_id',$value)->select('subject_name')->get()[0]->subject_name;
			}
	$response_array[0]=$sub;
	$response_array[1]=array();
	$response_array[6]=$exam[0]->to_from_range;
	$response_array[5]=$exam[0]->mark_file_long_string;
		}
		// return $response_array;
	$mark_file_long_string=$response_array[5];
	$subject=$response_array[0];
	$section=array_filter($response_array[1]);
	$to_from_range=$response_array[6];
	 $type="";
   $correctans=$exam;
   $correct=$correctans[0]['CorrectAnswer'];
if(isset($result[0]->Partial_String))
   $partial=$result[0]->Partial_String;
else
	$partial="";
	$all_sub_marks_array=static::get_marks_string($to_from_range,$mark_file_long_string,$correct);
	// return str_split($result[0]->Result_String);
	// return $all_sub_marks_array;

      $cal=[
       	"b"=>str_split($result[0]->Result_String),
       	"m"=>$all_sub_marks_array,
       	"r"=>explode(',',$to_from_range),
       	"s"=>$subject,
       	"se"=>$section,
       ];
       $analysis=static::strongweak($cal,$result,$exam[0]->max_marks);
       return static::markcount($cal,$analysis,$result,$partial,$mark_file_long_string);
    }

	public static function get_marks_string($to_from_range,$mark_file_long_string,$correct) //CRB not required
	{	
		$correct=explode(",",$correct);
		foreach($correct as $key=>$value)
		{
		   $otherarray[$key+1] = $value;
		}
		$to_from_range_array=explode(",",$to_from_range);
		$mark_file_array=explode(",",$mark_file_long_string);
		$to_from_range_array=explode(",",$to_from_range);
		$no_of_sub=sizeof($to_from_range_array);
		$loop_count=sizeof(explode(",",$mark_file_long_string))/4;
		$all_sub_marks_array=array();
		$from=0;
		$to=1;
		$mark=2;

		for($i=1;$i<=$loop_count;$i++)
		{ $one=$mark_file_array[$from];
		  $two=$mark_file_array[$to];
		  $this_mark=$mark_file_array[$mark];
		    for($m=$one;$m<=$two;$m++)
		    {	
		    	if(isset($otherarray[$m]) && $otherarray[$m]=='X'){
		       $all_sub_marks_array[]=0;
		   		}
		   		else{
		       $all_sub_marks_array[]=$this_mark;
		   		}
		    }
		$from=$from+4;
		$to=$to+4;
		$mark=$mark+4;
		}
		return $all_sub_marks_array;
	}
	public static function markcount($cal,$analysis,$result,$partial,$mark_file_long_string){
		// return $cal['s'];
		$m=3;
		$negative=explode(',', $mark_file_long_string);
		$neg_mark=$negative[$m];
		$ex=[
			"Right",
			"Wrong",
			"Unattempted",
			"Partial",
			"Grace",
			"Deleted",
			"Missed_Partial",
			"Negative",
			"Total"
		];
				$pa=0;

		if($partial=="")
		$partial="BCD-2,BD-2,ABD-3,ABC-3,CD-2,CD-2,BD-2,ABD-3,ABC-3,CD-2,CD-2";
		$parr=explode(",", $partial);

		$ar1=[
			"aa",
			"ab",
			"au",
			"ap",
			"ag",
			"ad",
			"am",
			"an",
			"at",
			];
		$extra=array();
		if(is_array($cal['s'])){
		$cal['s']=array_filter($cal['s']);		
		$ar2=array_values($cal['s']);
		}
		else{
		$ar2=$cal['s'];
		}
		// foreach ($cal['s'] as $key => $value) {
		// 	$value=strtoupper($value);
		// $extra['marks'][]=$result[0]->{$value};
		// $extra['subjects'][]=$value;
		// }
		$extra=$result[0]->TOTAL;

			if(isset($cal['s'][0]))
				$a=0;
			else
				$a=1;

			$b=0;$count=1;$sect=1;$secti="Section1";
			foreach ($cal['r'] as $key => $value) {
				$ra=explode('-',$value);
				$range[]=end($ra);
			}
				$ad=array();$ap=array();$au=array();$ag=array();$aa=array();$ab=array();	$am=array();	
				$subjects=$cal['s'][$a];
				if(!empty($cal['se']))
				$section=$cal['se'][$sect];
				else
				$section="";
				$xt=0;$gt=0;$pt=0;$ut=0;$rt=0;$wt=0;
				$t=array_sum($cal['m']);
		foreach ($cal['b'] as $key => $value) 
		{

			if(!empty($cal['se']))
					if($section!=$cal['se'][$sect])
					{
			
						$m=$m+4;
						if($count==4)
							$count=1;
					$count++;
					$section=$cal['se'][$sect];
					$secti="Section".$count;
					$neg_mark=$negative[$m];					
					}
					if($secti=="Section4")
						$secti="Section1";
					// return $cal['s'];
					// $a;
				if($range[$b]==$key){
					$a++;
					if(isset($cal['s'][$a]))
					$subjects=$cal['s'][$a];
					$b++;
					// return $subjects.$key;
				}
			if($value=="X" || $value=="D"){
					$ansd[$key]=$cal['m'][$key];

				$ad[$subjects][$secti]['Section']=$secti;
				// if(isset($ad[$subjects][$secti]['Total']))
				// $ad[$subjects][$secti]['Total']+=$cal['m'][$key];
				// else
				// $ad[$subjects][$secti]['Total']=$cal['m'][$key];

				if(isset($ad[$subjects][$secti]['Deleted']))
				{
				$ad[$subjects][$secti]['Deleted']+=$cal['m'][$key];
				}
				else
				{
				$ad[$subjects][$secti]['Deleted']=$cal['m'][$key];
				}
			}
			elseif($value=="G"){
					$ansd[$key]=$cal['m'][$key];

				$ad[$subjects][$secti]['Section']=$secti;
				if(isset($ad[$subjects][$secti]['Total']))
				$ad[$subjects][$secti]['Total']+=$cal['m'][$key];
				else
				$ad[$subjects][$secti]['Total']=$cal['m'][$key];

				if(isset($ad[$subjects][$secti]['Grace']))
				$ad[$subjects][$secti]['Grace']+=$cal['m'][$key];
				else
				$ad[$subjects][$secti]['Grace']=$cal['m'][$key];
			}
			elseif($value=="U"){
					$ansd[$key]=0;

				$ad[$subjects][$secti]['Section']=$secti;
				
				if(isset($ad[$subjects][$secti]['Unattempted']))
				$ad[$subjects][$secti]['Unattempted']+=$cal['m'][$key];
				else
				$ad[$subjects][$secti]['Unattempted']=$cal['m'][$key];
			}
			elseif($value=="P"){
				$ad[$subjects][$secti]['Section']=$secti;
				
				if(isset($ad[$subjects][$secti]['Partial'])){
					$ansd[$key]=intval(preg_replace('/[^0-9]+/', '',$parr[$pa]));

					if(isset($ad[$subjects][$secti]['Total']))
				$ad[$subjects][$secti]['Total']+=intval(preg_replace('/[^0-9]+/', '',$parr[$pa]));
				else
				$ad[$subjects][$secti]['Total']=intval(preg_replace('/[^0-9]+/', '',$parr[$pa]));

				$ad[$subjects][$secti]['Partial']+=intval(preg_replace('/[^0-9]+/', '',$parr[$pa]));
				$ad[$subjects][$secti]['Missed_Partial']+=$cal['m'][$key]-intval(preg_replace('/[^0-9]+/', '',$parr[$pa]));
		   	   $pa++;
				}
				else{
					$ansd[$key]=intval(preg_replace('/[^0-9]+/', '',$parr[$pa]));

					if(isset($ad[$subjects][$secti]['Total']))
				$ad[$subjects][$secti]['Total']+=intval(preg_replace('/[^0-9]+/', '',$parr[$pa]));
				else
				$ad[$subjects][$secti]['Total']=intval(preg_replace('/[^0-9]+/', '',$parr[$pa]));

				$ad[$subjects][$secti]['Partial']=intval(preg_replace('/[^0-9]+/', '',$parr[$pa]));
				$ad[$subjects][$secti]['Missed_Partial']=$cal['m'][$key]-intval(preg_replace('/[^0-9]+/', '',$parr[$pa]));
		   	   $pa++;

				}
			}
			elseif($value=="R"){
					$ansd[$key]=$cal['m'][$key];

				$ad[$subjects][$secti]['Section']=$secti;
				if(isset($ad[$subjects][$secti]['Total']))
				$ad[$subjects][$secti]['Total']+=$cal['m'][$key];
				else
				$ad[$subjects][$secti]['Total']=$cal['m'][$key];				

				if(isset($ad[$subjects][$secti]['Right']))
				$ad[$subjects][$secti]['Right']+=$cal['m'][$key];
				else
				$ad[$subjects][$secti]['Right']=$cal['m'][$key];
			}
			else{
				$ad[$subjects][$secti]['Section']=$secti;

				if(isset($ad[$subjects][$secti]['Total'])){
					if($neg_mark>0)
						$neg_mark=(-1 )* $neg_mark;
				$ad[$subjects][$secti]['Total']+=$neg_mark;
				}
				else{
					if($neg_mark>0)
						$neg_mark=(-1 )* $neg_mark;
				$ad[$subjects][$secti]['Total']=$neg_mark;
				}

				if(isset($ad[$subjects][$secti]['Negative']))
				$ad[$subjects][$secti]['Negative']+=$neg_mark;
				else
				$ad[$subjects][$secti]['Negative']=$neg_mark;

				if(isset($ad[$subjects][$secti]['Wrong']))
				$ad[$subjects][$secti]['Wrong']+=$cal['m'][$key];
				else
				$ad[$subjects][$secti]['Wrong']=$cal['m'][$key];

					$ansd[$key]=$neg_mark;


			}
			$sect++;

		}
		$ar3=$count;
		// foreach ($ar1 as $key => $value) 
		// {
			foreach ($ar2 as $key1 => $value1) 
			{
				for ($i=1; $i <=$count ; $i++) 
				{ 
					foreach ($ex as $key2 => $value2) {
					if(!isset($ad[$value1]['Section'.$i][$value2]))
						$ad[$value1]['Section'.$i][$value2]=0;
	   			$ad[$value1]['Section'.$i]['Section']='Sec'.$i;
						
					}
				}
			}
		// }
		$a=0;
		foreach ($ad as $key => $value) 
		{
			$ap[$a]['Subject']=strtoupper($key);
			for ($i=1; $i <= $count; $i++) { 
				foreach ($ex as $key1 => $value1) {
					if(isset($ap[$a][$value1]) && isset($value['Section'.$i][$value1]))
						$ap[$a][$value1]+=$value['Section'.$i][$value1];
					elseif(isset($value['Section'.$i][$value1]))
						$ap[$a][$value1]=$value['Section'.$i][$value1];

				}
			}
			ksort($value);
			$ap[$a]['Sectiondetails']=array_values($value);

			$a++;
		
		}

		return ['Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            ],
			"Total"=>$extra,
			// "Section_Count"=>$count,
			// "Subjects"=>$ar2,
			// "Right"=>$aa,
			// "Wrong"=>$ab,
			// "Unattempted"=>$au,
			// "Partial"=>$ap,
			// "Grace"=>$ag,
			"Data"=>$ap,
			"CorrectMark"=>$ansd,
			// "Missed_Partial"=>$am,
			// "Exam_Total_Mark"=>$t,
			"Analysis"=>$analysis,
		];
	}
	public static function strongweak($cal,$ans,$max){
		/*.......................Subject Wise List............................*/
		$range=DB::table('percentage_range')->where('id',1)->get();
		$range_from=$range[0]->range_from;
		$range_to=$range[0]->range_to;
		// return $range;
		if(is_array($cal['s']))
		{
			$cal['s']=array_filter($cal['s']);
			$cal['s']=array_values($cal['s']);
		}
		$a=0;
		$strong="";
		$weak="";
		$average="";
		$sectionstrong=array();
		$sectionweak=array();
		$max_marks=explode(',',$max);
		$perc=array();
		foreach ($cal['s'] as $key => $value) 
		{
			$perc[$value]=($ans[0]->{strtoupper($value)}/$max_marks[$a])*100;
			if($perc[$value]>$range_to)
				$strong.=substr($value,0,3).',';
			if($perc[$value]<$range_from)
				$weak.=substr($value,0,3).',';
			if($perc[$value]>=$range_from && $perc[$value]<=$range_to)
				$average.=substr($value,0,3).',';
			$a++;
		}
		/*.......................Section Wise List............................*/
	// 	foreach ($cal['r'] as $key => $value) {
	// 		$ra=explode('-',$value);
	// 		$range1[]=$ra[0];
	// 		$range[]=end($ra);
	// 	}
	// 	if($cal['se'])
	// 	for ($i=0; $i <count($range); $i++) { 	
	// 		for ($j=$range1[$i]; $j <=$range[$i]; $j++) { 
	//        		 $ran[$i][$j]=$cal['se'][$j];						
	// 		}	
	// 	}
	// 	$secmax=array();
	// 	$secmin=array();
	// 	$k=0;
	// 	$sec=array();
	// 	$l=0;
	// 	if(isset($ran))
	// 	foreach ($ran as $key1 => $value1) 
	// 	{	
	// 		$loop=array_values($ran[$key1]);
	// 		$ce=array_unique($ran[$key1]);
	// 		$ex=array_values($ce);
			
	// 		foreach ($value1 as $key2 => $value2) 
	// 		{
	// 			$sec[$value2][]=$cal['m'][$key2-1];
	// 			if($cal['b'][$key2-1]!="D" && $cal['b'][$key2-1]!="U" && $cal['b'][$key2-1]!="W" ){
	// 			$sec[$value2.'o'][]=$cal['m'][$key2-1];
	// 			}
	// 		}
	// 	$de=array();
	// 	if(!empty($sec))
	// 	foreach ($ex as $key => $value) {
	// 		if(isset($sec[$value]))
	// 		$de[$value]=array_sum($sec[$value]);
	// 		if(isset($sec[$value.'o']))
	// 		$de[$value.'o']=array_sum($sec[$value.'o']);
	// 	}
	// 	if(!empty($de))
	// 	foreach ($ex as $key => $value) {
	// 		if (array_key_exists($value.'o',$de))
	// 			$de[$value.'p']=($de[$value.'o']/$de[$value])*100;	
	// 			else	
	// 			$de[$value.'p']=30;	

	// 		if(isset($de[$value.'p'])){
	// 		if($de[$value.'p']>=75)
	// 			if(isset($sectionstrong[$key1]))
	// 			$sectionstrong[$key1].=$ex[$key].',';
	// 			else {
	// 			$sectionstrong[$key1]=$ex[$key].',';
	// 			}
	// 		elseif($de[$value.'p']<=60)
	// 			if(isset($sectionweak[$key1]))
	// 			$sectionweak[$key1].=$ex[$key].',';		
	// 			else
	// 			$sectionweak[$key1]=$ex[$key].',';		
	// 		else{}
	// 			}

	// 	}
	// 	unset($sec);
	// 	$sec=array();
	// }
	// $a=0;
	// foreach ($cal['s'] as $key => $value) {
	// 	if(isset($sectionweak[$key]))
	// 	$sectionweak['type'][]=$sectionweak[$key];
	// 	else
	// 	$sectionweak['type'][]="-";

	// 	$sectionweak['subjects'][]=$value;
	// 	unset($sectionweak[$key]);
	// 	if(isset($sectionstrong[$key]))
	// 	$sectionstrong['type'][]=$sectionstrong[$key];
	// 	else
	// 	$sectionstrong['type'][]="-";

	// 	$sectionstrong['subjects'][]=$value;
	// 	unset($sectionstrong[$key]);
	// }
		return [
			"range_from"=>$range_from,
			"range_to"=>$range_to,
			"weak_subject"=>strtoupper($weak),
			// "weak_section"=>$sectionweak,
			"average_subject"=>strtoupper($average),
			"strong_subject"=>strtoupper($strong),
			// "strong_section"=>$sectionstrong,
				];
	}
}
