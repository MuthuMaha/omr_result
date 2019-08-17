<?php

namespace App\OmrModels;
use DB;
use Auth;
use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
   
    protected $table='0_test_types';
    protected $primaryKey='test_type_id';

    public static function teacher_exam_info($data)
    {
    	if(isset($data->USER_ID))
    	$section=DB::table('IP_Exam_Section')
			    	->where('EMPLOYEE_ID',$data->USER_ID)
			    	->pluck('SECTION_ID');   
		else
		$section=DB::table('IP_Exam_Section')
			    	->where('EMPLOYEE_ID',Auth::user()->payroll_id)
			    	->pluck('SECTION_ID');

    	$exam=Exam::where('sl',$data->exam_id)->select('key_answer_file_long_string as CorrectAnswer','model_year','paper','omr_scanning_type','to_from_range','subject_string_final','sl','test_code','mode','mark_file_long_string','max_marks')->get();

    	$table=Mode::where('test_mode_id',$exam[0]->mode)->pluck('marks_upload_final_table_name');

    	$result=DB::table($table[0])
    				->join('scaitsqb.t_student_bio as st','st.ADM_NO','=',$table[0].'.STUD_ID')
    				->where('test_code_sl_id',$data->exam_id)
    				->wherein('st.SECTION_ID',$section)
    				->select($table[0].'.*')
    				->get();
    				// return $data->exam_id;
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
	$sub=DB::table('0_subjects')->wherein('subject_id',explode(',',$exam[0]->subject_string_final))->pluck('subject_name');
	$response_array[0]=$sub;
	$response_array[1]=array();
	$response_array[6]=$exam[0]->to_from_range;
	$response_array[5]=$exam[0]->mark_file_long_string;
		}
	$mark_file_long_string=$response_array[5];
	$subject=$response_array[0];
	$section=array_filter($response_array[1]);
	$to_from_range=$response_array[6];
	 $type="";
   $correctans=$exam;
   $correct=$correctans[0]['CorrectAnswer'];
	$all_sub_marks_array=static::get_marks_string($to_from_range,$mark_file_long_string,$correct);
	// return $mark_file_long_string;
	   $ar1=[
			"Right",
			"Wrong",
			"Negative",
			"Unattempted",
			"Partial",
			"Grace",
			"Deleted",
			"Missed_Partial",
			"Total"
			];
			if(is_array($subject))
		$ar2=array_values(array_filter($subject));
			else
				$ar2=$subject;
			if(is_array($section))	
		$ar3=count(array_unique($section));
			else
				$ar2=$section;
	foreach ($result as $key => $value) 
	{
	      $cal=[
	       	"b"=>str_split($result[$key]->Result_String),
	       	"m"=>$all_sub_marks_array,
	       	"r"=>explode(',',$to_from_range),
	       	"s"=>$subject,
	       	"se"=>$section,
	       ];
	       $markcount[]=static::markcount($cal,$result,$key,$mark_file_long_string);
	   }
	   // return $markcount;
	   $exact_ans=array();
	   if($ar3==0)
	   $ar3=1;
	   foreach ($ar1 as $key => $value) {
	   	$exact_ans['Section_Count']=$markcount[0]['Section_Count'];
	   	$exact_ans['Subjects']=$markcount[0]['Subjects'];
	   	 foreach ($ar2 as $key9 => $value9) {
	   	for ($i=1; $i <=$ar3 ; $i++) { 
	   		if(!empty($markcount[0]['Sectionwise_total'][$ar2[$key9]]['Section'.$i]))
	   		{
	   			$exact_ans['Sectionwise_total'][$ar2[$key9]]['Section'.$i]=array_sum($markcount[0]['Sectionwise_total'][$ar2[$key9]]['Section'.$i])*count($markcount);  
		   	}
		   	else
		   	{
   				$exact_ans['Sectionwise_total'][$ar2[$key9]]['Section'.$i]=0;
   			}
	  		 	}
		   }
		}
	
	foreach ($markcount as $key4 => $value4) 
		   {
	   foreach ($ar1 as $key => $value) {

	   	foreach ($ar2 as $key2 => $value2) {
	   		for ($i=1; $i <=$ar3 ; $i++) {
	   			if(!empty($markcount[$key4][$ar1[$key]][$ar2[$key2]]['Section'.$i])){
	   				if(isset($exact_ans[$ar1[$key]][$ar2[$key2]]['Section'.$i]))
	   			$exact_ans[$ar1[$key]][$ar2[$key2]]['Section'.$i]+=array_sum($markcount[$key4][$ar1[$key]][$ar2[$key2]]['Section'.$i]);  
	   			else
	   			$exact_ans[$ar1[$key]][$ar2[$key2]]['Section'.$i]=array_sum($markcount[$key4][$ar1[$key]][$ar2[$key2]]['Section'.$i]);  

	   			} 	
	   			else{
	   				if(isset($exact_ans[$ar1[$key]][$ar2[$key2]]['Section'.$i]))
	   				$exact_ans[$ar1[$key]][$ar2[$key2]]['Section'.$i]+=0;
	   				else
	   				$exact_ans[$ar1[$key]][$ar2[$key2]]['Section'.$i]=0;
	   			}

	   		}	   		
	   	}

	   foreach ($ar2 as $key8 => $value8) 
	   {
	   	if(isset($exact_ans['Subject_Total'][strtoupper($ar2[$key8])]))
	   	$exact_ans['Subject_Total'][strtoupper($ar2[$key8])]+=$markcount[$key4]['Subject_Total'][strtoupper($ar2[$key8])];
	   	else
	   	$exact_ans['Subject_Total'][strtoupper($ar2[$key8])]=$markcount[$key4]['Subject_Total'][strtoupper($ar2[$key8])];
	   }

	   	if(isset($exact_ans['Exam_Total_Mark']))
	   	$exact_ans['Exam_Total_Mark']+=$markcount[$key4]['Exam_Total_Mark'];
	   	else
	   	$exact_ans['Exam_Total_Mark']=$markcount[$key4]['Exam_Total_Mark'];
	  

	   }
	}
	// return $exact_ans;
	       $analysis=static::strongweak($exact_ans,$ar2,$section);

	   return $analysis;
    }

	public static function get_marks_string($to_from_range,$mark_file_long_string,$correct)
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
		    	if($otherarray[$m]=='X')
		       $all_sub_marks_array[]=0;
		   		else
		       $all_sub_marks_array[]=$this_mark;

		    }
		$from=$from+4;
		$to=$to+4;
		$mark=$mark+4;
		}
		return $all_sub_marks_array;
	}
	public static function markcount($cal,$result,$inc,$mark_file_long_string){
		$m=3;
		$negative=explode(',', $mark_file_long_string);
		$neg_mark=$negative[$m];

		$pa=0;
		if(isset($result[0]->Partial_String))
			if($result[0]->Partial_String=="")
			$partial="BCD-3,BD-2,ABD-3,ABC-3,CD-2,CD-2,BCD-3,BD-2,ABD-3,ABC-3,CD-2,CD-2";				

			else
			$partial=$result[0]->Partial_String;
		else
			$partial="BCD-3,BD-2,ABD-3,ABC-3,CD-2,CD-2,BCD-3,BD-2,ABD-3,ABC-3,CD-2,CD-2";

		$parr=explode(",", $partial);
		$extra=array();
		if(is_array($cal['s'])){
		$cal['s']=array_filter($cal['s']);		
		}
		foreach ($cal['s'] as $key => $value) {
			$value=strtoupper($value);
		$extra[$value]=$result[$inc]->{$value};
		}
		$extra['TOTAL']=$result[$inc]->TOTAL;

			if(isset($cal['s'][0]))
				$a=0;
			else
				$a=1;

			$b=0;$count=1;$sect=1;$secti="Section1";
			foreach ($cal['r'] as $key => $value) {
				$ra=explode('-',$value);
				$range[]=end($ra);
			}
				$ad=array();$ap=array();$au=array();$ag=array();$aa=array();$ab=array();$am=array();$ast=array();$an=array();	
				$subjects=$cal['s'][$a];
				if(!empty($cal['se']))
				$section=$cal['se'][$sect];
				else
				$section="";
				$xt=0;$gt=0;$pt=0;$ut=0;$rt=0;$wt=0;
				$t=array_sum($cal['m']);
				$te=array_unique($cal['se']);
		foreach ($cal['b'] as $key => $value) {
			if(!empty($cal['se']))
					if($section!=$cal['se'][$sect])
					{
						if($count==count($te)+1)
							$count=1;
					$count++;
					$section=$cal['se'][$sect];
					$secti="Section".$count;
					$neg_mark=$negative[$m];
					}
					if($secti=="Section4")
						$secti="Section1";
				if($range[$b]==$key){
					$b++;
					$a++;
					$subjects=$cal['s'][$a];
				}
				$at[$subjects][$secti][$key]=$cal['m'][$key];


			if($value=="X" || $value=="D"){

				$ad[$subjects][$secti][$key]=$cal['m'][$key];
			}
			elseif($value=="G"){
				$ast[$subjects][$secti][$key]=$cal['m'][$key];

				$ag[$subjects][$secti][$key]=$cal['m'][$key];
			}
			elseif($value=="U"){
				$au[$subjects][$secti][$key]=$cal['m'][$key];
			}
			elseif($value=="P"){	
				$ast[$subjects][$secti][$key]=intval(preg_replace('/[^0-9]+/', '',$parr[$pa]));

				$ap[$subjects][$secti][$key]=intval(preg_replace('/[^0-9]+/', '',$parr[$pa]));
				$am[$subjects][$secti][$key]=$cal['m'][$key]-intval(preg_replace('/[^0-9]+/', '',$parr[$pa]));	
			}
			elseif($value=="R"){
				$ast[$subjects][$secti][$key]=$cal['m'][$key];

				$aa[$subjects][$secti][$key]=$cal['m'][$key];		
			}
			else{
				if($neg_mark>0)
						$neg_mark=(-1 )* $neg_mark;
					
				$an[$subjects][$secti][$key]=$neg_mark;

				$ast[$subjects][$secti][$key]=$neg_mark;
				// $ast[$subjects][$secti][$key]=$cal['m'][$key];

				$ab[$subjects][$secti][$key]=$cal['m'][$key];			
			}
			$sect++;

		}
		if(is_array($cal['s']))
		$su=array_values($cal['s']);
		else
			$su=$cal['s'];
		return [
			'Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            ],
			"Sectionwise_total"=>$at,
			"Section_Count"=>$count,
			"Subjects"=>$su,
			"Right"=>$aa,
			"Wrong"=>$ab,
			"Negative"=>$an,
			"Unattempted"=>$au,
			"Partial"=>$ap,
			"Grace"=>$ag,
			"Deleted"=>$ad,
			"Missed_Partial"=>$am,
			"Total"=>$ast,
			"Subject_Total"=>$extra,
			"Exam_Total_Mark"=>$t,
		];
	}
	public static function strongweak($ans,$sub,$section)
	{	
		$range=DB::table('percentage_range')->where('id',1)->get();
		$range_from=$range[0]->range_from;
		$range_to=$range[0]->range_to;

		$cal['s']=$sub;
		$ar2=$sub;

	   $ar1=[
			"Right",
			"Wrong",
			"Negative",
			"Unattempted",
			"Partial",
			"Grace",
			"Deleted",
			"Missed_Partial",
			"Total"			
			];
	   foreach ($ar1 as $key => $value) {

	   	foreach ($cal['s'] as $key2 => $value2) {
	   		for ($i=1; $i <=$ans['Section_Count']; $i++) {
	   			if($ans['Sectionwise_total'][$ar2[$key2]]['Section'.$i]!=0)
	   			$ans[$ar2[$key2]]['Section'.$i][$ar1[$key]]=number_format((float) ($ans[$ar1[$key]][$ar2[$key2]]['Section'.$i]/$ans['Sectionwise_total'][$ar2[$key2]]['Section'.$i])*100, '2', '.', ''); 
	   			else
	   			$ans[$ar2[$key2]]['Section'.$i][$ar1[$key]]=0.01;
	   			$ans[$ar2[$key2]]['Section'.$i]['Section']='Sec'.$i;

	   	   		}	
	   			
	   		} 
	   }
		$se=array_values(array_unique($section));
		$section=$ans['Section_Count'];
		$ar=[
			"Partial",
			"Right",
			];
		/*.......................Subject Wise List............................*/
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
		$sectionstrong1=array();
		$sectionweak1=array();
		$perc=array();
		// foreach ($cal['s'] as $key => $value) {
		// 	$perc[$value]=($ans['Subject_Total'][strtoupper($value)]/array_sum($ans['Sectionwise_total'][$value]))*100;
		// 	if($perc[$value]>=75)
		// 		$strong.=$value.',';
		// 	if($perc[$value]<=60)
		// 		$weak.=$value.',';
		// 	$a++;
		// }
		// return $perc;
		unset($ans['Sectionwise_total']);
		unset($ans['Section_Count']);
		unset($ans['Subjects']);
		unset($ans['Subject_Total']);
		unset($ans['Exam_Total_Mark']);

	   foreach ($ar1 as $key => $value) 
	   {
	   	foreach ($cal['s'] as $key2 => $value2) 
	   	{
		if(isset($ans[$ar1[$key]][$ar2[$key2]]))
	   			unset($ans[$ar1[$key]]); 
	   	}
	   }
	   $ap=array();
	   $a=0;
	   $total=0;
		foreach ($ans as $key => $value) 
		{
			$ap[$a]['Subject']=strtoupper($key);
			for ($i=1; $i <= $section; $i++) { 
				foreach ($ar1 as $key1 => $value1) {
					if(isset($ap[$a][$value1]) && isset($value['Section'.$i][$value1]))
						$ap[$a][$value1]+=$value['Section'.$i][$value1]/$section; 
					elseif(isset($value['Section'.$i][$value1]))
						$ap[$a][$value1]=$value['Section'.$i][$value1]/$section;
					if(isset($ap[$a][$value1]))
					$ap[$a][$value1]=number_format((float)($ap[$a][$value1]),'2','.','');
				else
					$ap[$a][$value1]=0.00;
				}

			}
					$total+=($ap[$a]['Total']);
			$ap[$a]['Sectiondetails']=array_values($value);

			$a++;
		
		}
		foreach ($cal['s'] as $key => $value) {
			$perc[$value]=$ap[$key]['Total'];
			if($perc[$value]>$range_to)
				$strong.=substr($ap[$key]['Subject'],0,3).',';
			if($perc[$value]<$range_from)
				$weak.=substr($ap[$key]['Subject'],0,3).',';
			if($perc[$value]>=$range_from && $perc[$value]<=$range_to)
				$average.=substr($ap[$key]['Subject'],0,3).',';
			$a++;
		}
		return [
			'Login' => [
                            'response_message'=>"success",
                            'response_code'=>"1",
                            ],
			"Total"=>number_format((float)($total/count($cal['s'])),'2','.',''),
			"Data"=>$ap,
			// "Extra"=>$ans,
			"Analysis"=>[
			"range_from"=>$range_from,

			"range_to"=>$range_to,
				
			"weak_subject"=>strtoupper($weak),
			// "weak_section"=>$sectionweak,
			"average_subject"=>strtoupper($average),
			"strong_subject"=>strtoupper($strong),
			// "strong_section"=>$sectionstrong,
				],
			// "strong_subject"=>$strong,
				];
	}
}
